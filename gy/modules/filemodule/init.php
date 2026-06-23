<?php

use Gy\Core\Container;

if (!defined("GY_CORE") && (GY_CORE !== true)) die( "gy: err include core" );

/** 
 * описываю что есть в модуле 
 * - это на совести разработчика модуля
 * перечисляются имеющиеся классы и модули, а они лежат в стандартных папках 
 * (заранее обговорено какие есть разделы в модуле)
 */

// языковой файл
global $LANG;

$config = Container::getInstance()->getConfiguration();
include 'lang_init.php';

// компоненты которые есть в модуле
$componentsThisModule = array(
    'work_page_site'
);

// классы этого можуля
$classesThisModule = array(
    'Files',
    'SitePages',
    'AppFromConstructorPageComponent'
);

// страници админки
$adminPageThisModule = array(
    'work-page-site',
);

// кнопки для меню админки
$pagesFromAdminMenu = array(
    $mess[$LANG]['name-button'] => '/gy/admin/get-admin-page.php?page=work-page-site'
);

// пользовательское действие, и если оно разрешено текущему пользователю то он увидит 
// в меню админки кнопки $pagesFromAdminMenu
$isShowButtonsMenuAdminPanetThisModule = 'work_file_module';

// имя текущего модуля
$nameThisModule = 'filemodule';

//версия текущего модуля
$versionThisModule = '0.1';
