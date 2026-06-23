<?php

declare(strict_types=1);

use Gy\Core\Application;
use Gy\Core\Configuration;
use Gy\Core\Container;
use Gy\Core\Db\PgSql;
use Gy\Core\Db\PhpFileSqlClientForGy;
use Gy\Core\Security;
use Gy\Core\Storage\Mysql\MySql;
use Gy\Core\User\AccessUserGroup;
use Gy\Core\User\User;

// если ядро не подключено подключаем всё а если уже подключено то не надо
if (!defined('GY_CORE')) {
    ob_start();
    define('GY_CORE', true); // флаг о том что ядро подключено // flag include core

    // подключение настроек ядра // include options
    $config = Configuration::createFromFile(__DIR__ . 'config/gy_config.php');

    // добавлю версию ядра gy
    $config->version = '0.4-alpha';

    $application = new Application($config);

    if (in_array($config['lang'], ['rus', 'eng'])) {
        global $LANG;
        $LANG = $config->language;
    }
    // обезопасить получаемый конфиг

    global $APP;
    $APP = $application;

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
    $container = new Container($APP, $db, $config);

    // подключить модули (пока сразу все)
    $moduleManager = $container->getModuleManager();
    $moduleManager->setUrlGyCore(__DIR__);
    $moduleManager->includeAllModules();

    // авто подключение классов // кроме подключения классов модулей используется psr0
    spl_autoload_register($moduleManager->autoloadModuleClass(...));

    global $cryptoService;
    $cryptoService = Container::getInstance()->getCryptoService();
    if ($config->salt !== '') {
        $cryptoService->setSalt($config->salt);
    }

    global $USER;
    $USER = new User();

    // объявить имя класса для кеша // TODO пока так но сделать надо получше (заменить на фабрику или ещё какой патерн)
    if (!isset($APP->configuration['type_cache'])) {
        $config->cacheType = 'cacheFiles';
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
    if ($config->appSecret !== null) {
        $isAdminPageGy = strripos($_SERVER['REQUEST_URI'], '/gy/') !== false;
        $validSecretKey = ($_REQUEST['secretKeyAdminPanel'] ?? null) === $config->appSecret;

        if (
            $isAdminPageGy
            && !$validSecretKey
            && !AccessUserGroup::accessThisUserByAction('show_admin_panel')
        ) {
            $page404 = $config->projectRoot . DIRECTORY_SEPARATOR . $APP->configuration['urlPage404'];
            if (!empty($page404)) {
                include($page404);
            } else {
                include('404.php');
            }
        }
    }
}

