<?php
if (!defined("GY_CORE") && (GY_CORE !== true)) die( "gy: err include core" );

use Gy\Core\Localization;

global $APP;
global $USER;

$langTextThisFile = new Localization(
    $APP->urlProject."/gy/lang", 
    'header-admin', 
    $APP->options['lang']
);
?>

<html>
    <head>
        <title>gy -admin</title>
        <link href="../../gy/style/main.css" rel="stylesheet">
        <script src="../../gy/js/main.js"></script>
    </head>
    <body class="gy-body-admin">
        
        
        
        <h2 class="gy-admin-logo">
            <?=$langTextThisFile->getMessage('title')?>
        </h2>
        <?php
        if (!empty($APP->options['v-gy'])) {
        ?>
            <span class="version-gy-core">v <?=$APP->options['v-gy']?></span>
            <br/>
        <?php
        }
        ?>
        <a href="/" class="gy-admin-button-min" >
            <?=$langTextThisFile->getMessage('site')?>
        </a>
        <br/>
        <br/>
        <?php
        if (Gy\Core\User\AccessUserGroup::accessThisUserByAction( 'show_admin_panel')) {

            // меню доступное для текущего пользователя
            $buttonName = $langTextThisFile->getMessage('index-page');
            $menu[$buttonName] = '/gy/admin/index.php';

            if (Gy\Core\User\AccessUserGroup::accessThisUserByAction( 'edit_users') 
                || $USER->isAdmin()
            ) {
                $buttonName = $langTextThisFile->getMessage('users');
                $menu[$buttonName] = '/gy/admin/users.php';
            }

            // надо добавить пункты меню заданные в подключенных модулях
            $module = Gy\Core\ModuleManager::getInstance();
            foreach ($module->getButtonsMenuAllModules() as $nameModule => $arButton) {
                // условия показа пункта меню (задаётся модулем) или если админ
                if (
                    (!empty($module->getFlagShowButtonsAdminPanelByModule[$nameModule])
                     && Gy\Core\User\AccessUserGroup::accessThisUserByAction( 
                            $module->getFlagShowButtonsAdminPanelByModule[$nameModule]
                        ) 
                    )
                    || $USER->isAdmin()
                ) {
                    foreach ($arButton as $buttonName => $buttonUrl) {
                        $menu[$buttonName] = $buttonUrl;
                    }
                }
            }

            $menu[ $langTextThisFile->getMessage('modules') ] = '/gy/admin/modules.php';
            
            if ($USER->isAdmin()) {
                $menu[ $langTextThisFile->getMessage('options') ] = '/gy/admin/options.php';
            }

            // menu
            $APP->component(
                'menu',
                '0',
                array(
                    'buttons' => $menu
                )
            );
        }
