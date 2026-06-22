<?php

use Gy\Core\App;
use Gy\Core\Security;
use Gy\Core\Crypto;
use Gy\Core\Module;
use Gy\Core\User\User;

// если ядро не подключено подключаем всё а если уже подключено то не надо
if ( !defined("GY_CORE") ) {

    ob_start();
    define("GY_CORE", true); // флаг о том что ядро подключено // flag include core

    include_once("config/gy_config.php"); // подключение настроек ядра // include options

    if (in_array($gyConfig['lang'], array('rus', 'eng'))) {
        global $LANG;
        $LANG = $gyConfig['lang'];
    }

    // путь к проекту
    global $URL_PROJECT;
    $URL_PROJECT = substr(__DIR__, 0, (strlen(__DIR__) - 3) );
    
    // авто подключение классов // кроме подключения классов модулей используется psr0
    function autoload($className)
    {
        //   1. для модулей завести пространство имён типа Gy\Modules\<имя модуля>\Classes\<имя класса>
        //   2. потом подключать вначале customDir/vendor
        //   3. уже потом из раздела gy/classes
  
        global $URL_PROJECT;
        
        // из пространства имён составляю путь к классу
        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        // определяю является ли путь и вызываемый класс, классом модуля,
        //   если так то подключаю класс модуля 
        //   пространство имён будет: Gy\Modules\<имя модуля>\Classes\<имя класса>
        
        // условие регулярки для такого пространства имён 
        $br = preg_quote(DIRECTORY_SEPARATOR);
        $pattern = "#^(Gy".$br."Modules".$br.")(.*)(".$br."Classes".$br.")(.*).php#";
              
        //var_dump(  "#^Gy".DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR."Modules".DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR."#" );
        $parseUrl = array(); // тут результат парсинга
        
        if (preg_match( $pattern, $fileName, $parseUrl) == 1) {
            //$parseUrl[2] - тут имя модуля
            //$parseUrl[4] - Тут имя класса
            
            // TODO можно было бы подключить конкретный модуль но пока оставлю старую механику 
            //   (когда подключаются все сразу)
            
            // проверю есть ли класс в подключённых модулях и подключу (в модулях psr0 нет)
            $module = Module::getInstance();
            $meyByClassModule = $module->getUrlModuleClassByNameClass($parseUrl[4]);
            if ($meyByClassModule !== false) {
                require_once( $meyByClassModule );
            } else {
                //die('!Error class '.$className.' not found'); 
            }
        } elseif (file_exists($URL_PROJECT.DIRECTORY_SEPARATOR.'customDir'.DIRECTORY_SEPARATOR.'classes/'.DIRECTORY_SEPARATOR.$fileName)) {
            // иначе, если не класс модуля, ищу класс в разделе для кастомных (пользовательских) классов
            require_once $URL_PROJECT.DIRECTORY_SEPARATOR.'customDir'.DIRECTORY_SEPARATOR.'classes/'.DIRECTORY_SEPARATOR.$fileName;
        } elseif (file_exists($URL_PROJECT.DIRECTORY_SEPARATOR.'gy/classes'.DIRECTORY_SEPARATOR.$fileName)) { 
            // иначе ищу класс в классах gy
            require_once 'classes'.DIRECTORY_SEPARATOR.$fileName;
        }

    }
    spl_autoload_register('autoload');
      
    // подключить модули (пока сразу все)
    $module = Module::getInstance();
    $module->setUrlGyCore(__DIR__);
    //$module->includeModule('containerdata'); - так подключается конкретный модуль
    $module->includeAllModules();
    
    // обезопасить получаемый конфиг
    $gyConfig = Security::filterInputData($gyConfig);

    global $APP;
    // добавлю версию ядра gy
    $gyConfig['v-gy'] = '0.4-alpha';
    $APP = App::createApp($URL_PROJECT, $gyConfig);
    unset($gyConfig);

    // подключить класс работы с базой данный // include class work database
    if (isset($APP->options['db_config'])
        && isset($APP->options['db_config']['db_type'])
        && isset($APP->options['db_config']['db_host'])
        && isset($APP->options['db_config']['db_user'])
        && isset($APP->options['db_config']['db_pass'])
        && isset($APP->options['db_config']['db_name'])
    ) {
        global $DB;
        
        if ($APP->options['db_config']['db_type'] == 'MySql') {
            $DB = new Gy\Core\Db\MySql($APP->options['db_config']); 
        } elseif ($APP->options['db_config']['db_type'] == 'PgSql') {
            $DB = new Gy\Core\Db\PgSql($APP->options['db_config']); 
        } elseif ($APP->options['db_config']['db_type'] == 'PhpFileSqlClientForGy') {
            $DB = new Gy\Core\Db\PhpFileSqlClientForGy($APP->options['db_config']); 
        }
 
    }   
    
    global $CRYPTO;
    $CRYPTO = new Crypto();
    if (!empty($APP->options['sole'])) {
        $CRYPTO->setSole($APP->options['sole']);
    }

    global $USER;
    $USER = new User();

    // объявить имя класса для кеша // TODO пока так но сделать надо получше (заменить на фабрику или ещё какой патерн)
    if (!isset($APP->options['type_cache'])) {
        $APP->options['type_cache'] = 'cacheFiles';
    }
    global $CACHE_CLASS_NAME;
    $CACHE_CLASS_NAME = 'Gy\\Core\\Cache\\'.$APP->options['type_cache'];   

    session_start();

    // нужно обезопасить все входные данные
    // на этой странице не проверять, т.к. там могут сохраняться данные html (своства контейнера данных)
    // TODO - может как то это пофиксить
    if( ($APP->getUrlTisPageNotGetProperty() != '/gy/admin/get-admin-page.php')
        && ( !empty($_REQUEST['page']) && ($_REQUEST['page'] != 'container-data-element-property') )
    ) {
        $_REQUEST = Security::filterInputData($_REQUEST);
        $_GET = Security::filterInputData($_GET);
        $_POST = Security::filterInputData($_POST);
    }

    
    // подключаю customDir/gy/afterGyCore.php если он есть, тут можно кастомное дописать 
    //   или обьявить psr4 автозакрузку классов из любой желаемой директории
    if (file_exists($URL_PROJECT.DIRECTORY_SEPARATOR.'customDir'.DIRECTORY_SEPARATOR.'gy'.DIRECTORY_SEPARATOR.'afterGyCore.php')) {
        require_once $URL_PROJECT.DIRECTORY_SEPARATOR.'customDir'.DIRECTORY_SEPARATOR.'gy'.DIRECTORY_SEPARATOR.'afterGyCore.php';
    }
   
    // если задан secretKeyAuthorizationAdminPanel для админки, то без этого ключа (secretKeyAdminPanel в урле) не пускать в админку
    if (!empty($APP->options['secretKeyAuthorizationAdminPanel']) ) {
        
        $isAdminPageGy = strripos($_SERVER['REQUEST_URI'], '/gy/') !== false;
        $isTrueSecretKeyAuthorizationAdminPanel = ( !empty($_REQUEST['secretKeyAdminPanel']) && ($_REQUEST['secretKeyAdminPanel'] == $APP->options['secretKeyAuthorizationAdminPanel']));
                
        if (
            $isAdminPageGy 
            && ( !$isTrueSecretKeyAuthorizationAdminPanel && !Gy\Core\User\AccessUserGroup::accessThisUserByAction( 'show_admin_panel') 
            )
        ) {
            if (!empty($URL_PROJECT.DIRECTORY_SEPARATOR.$APP->options['urlPage404'])) {
                include($URL_PROJECT.DIRECTORY_SEPARATOR.$APP->options['urlPage404']);
            } else {
                include('404.php');
            }
        }
    }
}

