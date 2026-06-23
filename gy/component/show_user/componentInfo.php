<?php
if (!defined("GY_CORE") && (GY_CORE !== true)) die( "gy: err include core" );

use Gy\Core\Localization;

global $APP;
$utlThisComponent = "/gy/component/show_user/";
$langComponentInfo = new Localization($APP->urlProject.$utlThisComponent, 'componentInfo', $APP->options['lang']);

$componentInfo = array(
    'name' => 'show_user',
    'text-info' => $langComponentInfo->getMessage('text-info'),
    'v' => '0.1',
    'all-property' => array(
        'id'
    ),
    'all-property-text' => array(
        'id' => $langComponentInfo->getMessage('property-id-user')
    )
);
