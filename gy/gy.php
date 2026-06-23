<?php

declare(strict_types=1);

use Gy\Core\App;
use Gy\Core\Configuration;
use Gy\Core\Db\MySql;
use Gy\Core\Db\PgSql;
use Gy\Core\Db\PhpFileSqlClientForGy;
use Gy\Core\ModuleManager;
use Gy\Core\Security;
use Gy\Core\ServiceLocator;
use Gy\Core\User\User;

// если ядро не подключено подключаем всё а если уже подключено то не надо
if (!defined('GY_CORE')) {
    ob_start();
    define('GY_CORE', true); // флаг о том что ядро подключено // flag include core

    // подключение настроек ядра // include options
    $config = Configuration::createFromFile(__DIR__ . 'config/gy_config.php');

    if (in_array($config['lang'], ['rus', 'eng'])) {
        global $LANG;
        $LANG = $config->language;
    }

    // путь к проекту
    global $URL_PROJECT;
    $URL_PROJECT = substr(__DIR__, 0, (strlen(__DIR__) - 3));

    // авто подключение классов // кроме подключения классов модулей используется psr0
    function autoload($className)
    {
        //   1. для модулей завести пространство имён типа Gy\Modules\<имя модуля>\Classes\<имя класса>
        //   2. потом подключать вначале customDir/vendor
        //   3. уже потом из раздела gy/classes

        global $URL_PROJECT;

        // из пространства имён составляю путь к классу
        $className = ltrim($className, '\\');
        $fileName = '';
        $namespace = '';
        if ($lastNsPos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        // определяю является ли путь и вызываемый класс, классом модуля,
        //   если так то подключаю класс модуля 
        //   пространство имён будет: Gy\Modules\<имя модуля>\Classes\<имя класса>

        // условие регулярки для такого пространства имён 
        $br = preg_quote(DIRECTORY_SEPARATOR);
        $pattern = "#^(Gy" . $br . "Modules" . $br . ")(.*)(" . $br . "Classes" . $br . ")(.*).php#";

        //var_dump(  "#^Gy".DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR."Modules".DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR."#" );
        $parseUrl = []; // тут результат парсинга

        if (preg_match($pattern, $fileName, $parseUrl) == 1) {
            //$parseUrl[2] - тут имя модуля
            //$parseUrl[4] - Тут имя класса

            // TODO можно было бы подключить конкретный модуль но пока оставлю старую механику 
            //   (когда подключаются все сразу)

            // проверю есть ли класс в подключённых модулях и подключу (в модулях psr0 нет)
            $moduleManager = ModuleManager::getInstance();
            $meyByClassModule = $moduleManager->getUrlModuleClassByNameClass($parseUrl[4]);
            if ($meyByClassModule !== false) {
                require_once($meyByClassModule);
            } else {
                //die('!Error class '.$className.' not found'); 
            }
        } elseif (file_exists(
            $URL_PROJECT . DIRECTORY_SEPARATOR . 'customDir' . DIRECTORY_SEPARATOR . 'classes/' . DIRECTORY_SEPARATOR . $fileName,
        )) {
            // иначе, если не класс модуля, ищу класс в разделе для кастомных (пользовательских) классов
            require_once $URL_PROJECT . DIRECTORY_SEPARATOR . 'customDir' . DIRECTORY_SEPARATOR . 'classes/' . DIRECTORY_SEPARATOR . $fileName;
        } elseif (file_exists($URL_PROJECT . DIRECTORY_SEPARATOR . 'gy/classes' . DIRECTORY_SEPARATOR . $fileName)) {
            // иначе ищу класс в классах gy
            require_once 'classes' . DIRECTORY_SEPARATOR . $fileName;
        }
    }

    spl_autoload_register('autoload');

    // подключить модули (пока сразу все)
    $module = ModuleManager::getInstance();
    $module->setUrlGyCore(__DIR__);
    //$module->includeModule('containerdata'); - так подключается конкретный модуль
    $module->includeAllModules();

    // обезопасить получаемый конфиг

    global $APP;
    // добавлю версию ядра gy
    $config->version = '0.4-alpha';
    $APP = new App($config->projectRoot, $config);

    // подключить класс работы с базой данный // include class work database
    if (
        isset($config->databaseConfig['db_type'])
        && isset($config->databaseConfig['db_host'])
        && isset($config->databaseConfig['db_user'])
        && isset($config->databaseConfig['db_pass'])
        && isset($config->databaseConfig['db_name'])
    ) {
        global $DB;

        $db = match ($config->databaseConfig['db_type']) {
            'MySql' => new MySql($config->databaseConfig),
            'PgSql' => new PgSql($config->databaseConfig),
            'PhpFileSqlClientForGy' => new PhpFileSqlClientForGy($config->databaseConfig),
        };
    } else {
        throw new RuntimeException('Database connection must be present');
    }

    $serviceLocator = new ServiceLocator($APP, $db, $config);

    global $cryptoService;
    $cryptoService = ServiceLocator::getInstance()->getCryptoService();
    if ($config->salt !== '') {
        $cryptoService->setSalt($APP->configuration['sole']);
    }

    global $USER;
    $USER = new User();

    // объявить имя класса для кеша // TODO пока так но сделать надо получше (заменить на фабрику или ещё какой патерн)
    if (!isset($APP->configuration['type_cache'])) {
        $APP->configuration['type_cache'] = 'cacheFiles';
    }
    global $CACHE_CLASS_NAME;
    $CACHE_CLASS_NAME = 'Gy\\Core\\Cache\\' . $APP->configuration->cacheType;

    session_start();

    // нужно обезопасить все входные данные
    // на этой странице не проверять, т.к. там могут сохраняться данные html (своства контейнера данных)
    // TODO - может как то это пофиксить
    if (($APP->getUrlTisPageNotGetProperty() != '/gy/admin/get-admin-page.php')
        && (!empty($_REQUEST['page']) && ($_REQUEST['page'] != 'container-data-element-property'))
    ) {
        $_REQUEST = Security::filterInputData($_REQUEST);
        $_GET = Security::filterInputData($_GET);
        $_POST = Security::filterInputData($_POST);
    }

    // подключаю customDir/gy/afterGyCore.php если он есть, тут можно кастомное дописать 
    //   или обьявить psr4 автозакрузку классов из любой желаемой директории
    $initHook = implode(
        DIRECTORY_SEPARATOR,
        [$config->projectRoot, 'customDir', 'gy' , 'afterGyCore.php']
    );
    if (is_file($initHook)) {
        require_once $initHook;
    }

    // если задан secretKeyAuthorizationAdminPanel для админки, то без этого ключа (secretKeyAdminPanel в урле) не пускать в админку
    if (!empty($APP->configuration['secretKeyAuthorizationAdminPanel'])) {
        $isAdminPageGy = strripos($_SERVER['REQUEST_URI'], '/gy/') !== false;
        $isTrueSecretKeyAuthorizationAdminPanel = (!empty($_REQUEST['secretKeyAdminPanel']) && ($_REQUEST['secretKeyAdminPanel'] == $APP->configuration['secretKeyAuthorizationAdminPanel']));

        if (
            $isAdminPageGy
            && (!$isTrueSecretKeyAuthorizationAdminPanel && !Gy\Core\User\AccessUserGroup::accessThisUserByAction(
                    'show_admin_panel',
                )
            )
        ) {
            if (!empty($URL_PROJECT . DIRECTORY_SEPARATOR . $APP->configuration['urlPage404'])) {
                include($URL_PROJECT . DIRECTORY_SEPARATOR . $APP->configuration['urlPage404']);
            } else {
                include('404.php');
            }
        }
    }
}

