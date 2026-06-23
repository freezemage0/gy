<?php
if (!defined("GY_CORE") && (GY_CORE !== true)) die( "gy: err include core" );

use Gy\Core\Localization;

global $APP;
$utlThisComponent = "/gy/modules/containerdata/component/containerdata_edit/";
$langComponentInfo = new Localization($APP->urlProject.$utlThisComponent, 'componentInfo', $APP->options['lang']);

$componentInfo = array(
    'name' => 'containerdata_edit',
    'text-info' => $langComponentInfo->getMessage('text-info'),
    'v' => '0.1',
    'all-property' => array(
        'ID'
    ),
    'all-property-text' => array(
        'ID' => $langComponentInfo->getMessage('property-ID')
    )
);
