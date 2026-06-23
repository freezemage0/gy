<?php 
if (!defined("GY_CORE") && (GY_CORE !== true)) die( "gy: err include core" );

// подключить даныне по всем подключенным модулям
$module = Gy\Core\ModuleManager::getInstance();
$arRes['info-modules'] = $module->getInfoAllIncludeModules();

// показать шаблон
$this->template->show($arRes, $this->arParam);
