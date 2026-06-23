<?php 
if (!defined("GY_CORE") && (GY_CORE !== true)) die( "gy: err include core" );

use Gy\Core\User\UserPropertyStorage;

$data = $_REQUEST;

// получить все возможные типы свойств
$arRes['allTypePropertys'] = UserPropertyStorage::getAllTypeAllUsersPropertys();

// получить все общие свойства пользователей которые были созданы
$arRes['allUsersCreatePropertys'] = UserPropertyStorage::getAllGeneralUsersPropertys();

// получить значения свойств конкретного пользователя
$arRes['valuePropertysThisUser'] = UserPropertyStorage::getAllValueUserProperty( $this->arParam['id-user'], 'text'); // text - т.к. пока только такие типы свойств реализованы

// собираю общий массив
$arRes['propertys'] = array();
foreach ($arRes['allUsersCreatePropertys'] as $key => $value) {

    $val = '';
    if (!empty($arRes['valuePropertysThisUser'][$value['id']])) {
        $val = $arRes['valuePropertysThisUser'][$value['id']]['value'];
    }

//    $id = '-';
//    if(!empty($arRes['valuePropertysThisUser'][$value['id']])){
//        $id = $arRes['valuePropertysThisUser'][$value['id']]['id'];
//    }

    $arRes['propertys'][] = array(
        'name_property' => $value['name_property'],
        //'id' => $id,
        'id_property' => $value['id'],
        'value' => $val
    );
}

// проверка пришедшие значения свойств, есть ли такие свойства
function isTrueDataInProperty($propertys, $allUsersCreatePropertys){
    $result = true;
    foreach ($propertys as $idProperty => $value) {
        if (!isset($allUsersCreatePropertys[$idProperty])) {
            $result = false;
        }
    }
    return $result;
}

// сохраняем пришедшее
if (
    !empty($data['edit-id']) 
    && is_numeric($data['edit-id'])
    && !empty($data['id-user'])
    && is_numeric($data['id-user'])
    && ($data['edit-id'] == $data['id-user'])
    && !empty($data['property'])
    && is_array($data['property'])
    && isTrueDataInProperty($data['property'], $arRes['allUsersCreatePropertys'])
) {
    foreach ($data['property'] as $idProperty => $value) {
        if ($arRes['valuePropertysThisUser'][$idProperty]) { // было ли уже задано когда то такое значение, для такого своства
            // если да то обновляем то что есть уже
            UserPropertyStorage::updateValueProperty($data['id-user'], 'text', $idProperty, $value);
        } else {
            // если нет создаём новое значение
            UserPropertyStorage::addValueProperty($data['id-user'], 'text', $idProperty, $value);
        }
    }
    $arRes['stat'] = 'ok';
    // TODO может обработать возможные ошибки
}


// показать шаблон
$this->template->show($arRes, $this->arParam);
