<?php
if (!defined("GY_CORE") && (GY_CORE !== true)) die( "gy: err include core" );

use Gy\Core\Localization;

global $APP;
$utlThisComponent = "/gy/component/hed-meta-tag-seo/";
$langComponentInfo = new Localization($APP->urlProject.$utlThisComponent, 'componentInfo', $APP->options['lang']);

$componentInfo = array(
    'name' => 'hed-meta-tag-seo',
    'text-info' => $langComponentInfo->getMessage('text-info'),
    'v' => '0.1',
);
