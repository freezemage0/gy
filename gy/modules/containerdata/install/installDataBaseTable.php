<?php

use Gy\Modules\containerdata\Classes\ContainerData;

if (!defined("GY_CORE") && (GY_CORE !== true)) die( "gy: err include core" );

global $argv;
$isRunConsole = isset($argv);
$br = "\n";

if ($isRunConsole) {

//    include __DIR__."/../gy.php"; // подключить ядро // include core

    global $DB;

    //---containerData---
    echo $br.$br.'--install table module - containerData = start--';

    echo $br.'install table module - containerData = start';
    $res = $DB->createTable( // containerData-ы
        'container_data',
        array( 
            'id int PRIMARY KEY AUTO_INCREMENT', 
            'name varchar(64)', 
            'code varchar(64)', 
        )
    );

    $res = $DB->createTable( // список свойств containerData
        'list_propertys_container_data',
        array(
            'id int PRIMARY KEY AUTO_INCREMENT',
            'id_type_property int',
            'id_container_data int',
            'code varchar(64)',
            'name varchar(64)',
        )
    );

    $res = $DB->createTable( // типы свойств containerData
        'types_property_container_data',
        array(
            'id int PRIMARY KEY AUTO_INCREMENT',
            'info varchar(64)',
            'code varchar(64)',
            'name varchar(64)',
            'name_table varchar(64)'
        )
    );

    $res = $DB->insertDb(
        'types_property_container_data',
        array(
            'name' => 'html',
            'code' => 'html',
            'info' => 'property save date - html',
            'name_table' => 'value_propertys_type_html'
        )
    );

    $res = $DB->insertDb(
        'types_property_container_data',
        array(
            'name' => 'number',
            'code' =>   'number',
            'info' => 'property save date - number',
            'name_table' => 'value_propertys_type_number'
        )
    );
    
    $res = $DB->insertDb(
        'types_property_container_data', // TODO названия таблиц брать из класса ContainerData
        array(
            'name' => 'public file',
            'code' =>   'file',
            'info' => 'property save date - file',
            'name_table' => 'value_propertys_type_public_file'
        )
    );

    $res = $DB->createTable( // элементы containerData-а
        'element_container_data',
        array(
            'id int PRIMARY KEY AUTO_INCREMENT',
            'section_id int',
            'code varchar(64)',
            'name varchar(64)',
            'id_container_data int',
        )
    );

    $res = $DB->createTable( // значения свойств containerData-а типа строка
        'value_propertys_type_html',
        array(
            'id int PRIMARY KEY AUTO_INCREMENT',
            'id_container_data int',
            'id_element_container_data int',
            'id_property_container_data int',
            'value varchar(2500)'
        )
    );

    $res = $DB->createTable( // значения свойств containerData-а типа число
        'value_propertys_type_number',
        array(
            'id int PRIMARY KEY AUTO_INCREMENT',
            'id_container_data int',
            'id_element_container_data int',
            'id_property_container_data int',
            'value int'
        )
    );
    
    $res = $DB->createTable( // значения свойств containerData-а типа file
        'value_propertys_type_public_file',
        array(
            'id int PRIMARY KEY AUTO_INCREMENT',
            'id_container_data int',
            'id_element_container_data int',
            'id_property_container_data int',
            'value varchar(1000)',
        )
    );
    echo $br.'install table module - containerData = OK!';
    //-containerData-------------
    
    
    //--тестовый контент--
    echo $br.'install test content - containerData = start';

    // добавить контейнер данных - контент
    ContainerData::addContainerData(array('code'=> 'Content','name'=> 'Контент2 | Content2'));

    $dataContentContainerData = ContainerData::getContainerData(array('=' => array('code', "'Content'")), array('*'));

    //добавить свойство
    ContainerData::addPropertyContainerData(
        array(
            'id_type_property' => 1,
            'id_container_data' => $dataContentContainerData[0]['id'],
            'code' => 'html-code',
            'name' => 'html вставка | html code'
        )
    );

    // добавить элемент контейнера данных
    ContainerData::addElementContainerData(
        array(
            'section_id' => 0,
            'code' => 'html-index-page',
            'name' => 'Приветствие на главной | Welcome on the main',
            'id_container_data' => $dataContentContainerData[0]['id']
        )
    );

    // взять типы свойств что бы знать названия таблиц где их искать
    //$dataTypeProperty = ContainerData::getAllTypePropertysContainerData();
    // найти элемент
    $dataElement = ContainerData::getElementContainerData(
        array(
            'AND' => array(
                array( '=' => array( 'id_container_data', $dataContentContainerData[0]['id']) ),
                array( '=' => array( 'code', "'html-index-page'"))
            )
        )
    );

    // найти его свойства
    $propertyContainerData = ContainerData::getPropertysContainerData(
        array(
            '='=>array(
                'id_container_data', 
                $dataContentContainerData[0]['id']
            ) 
        ) 
    );
    $prop = array_shift($propertyContainerData);

    // добавить значение свойства для элемента созданного выше
    ContainerData::addValuePropertyContainerData(
        $dataContentContainerData[0]['id'],
        $dataElement['id'],
        $prop['id'],
        'value_propertys_type_html',
        'Привет пользователь, тебя приветствует gy php framework'.$br.' и текст показан из его контентной части!!!'.
            '| Hello user, you are greeted by the gy php framework and the text is shown from its content part !!!'
    );

    echo $br.'install test content - containerData = OK!';
    ////

    echo $br.'add user group and action and user - containerData = start';
    // группы пользователей и права на действия
    $DB->insertDb(
        'action_user',
        array(
            'code' => 'edit_container_data',
            'text' => 'Изменение всех container-data | Edit all container-data',
        )
    );

    $DB->insertDb(
        'users_in_groups',
        array(
            'code_group' => 'content',
            'id_user' => 2,
        )
    );

    $DB->insertDb(
        'access_group',
        array(
            'code' => 'content',
            'name' => 'Контент | Сontent',
            'text' => 'Те кто изменяют контент сайта | Those who can change the content of the site',
            'code_action_user' => 'edit_container_data'
        )
    );
    
    echo $br.'add user group and action and user - containerData = OK!';


    echo $br.'--install table module - containerData = OK!--'.$br;

} else {
    echo '! Error. You need to run the script in the console';

}