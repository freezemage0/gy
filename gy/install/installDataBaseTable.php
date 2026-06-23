<?php
// TODO проверку на ошибки переделать с учётом установки на пострис

global $argv;
$isRunConsole = isset($argv);
$BR = "\n";

if ($isRunConsole) {

    include __DIR__."/../gy.php"; // подключить ядро // include core

    echo $BR.'-----install gy core taldes db-----';
    echo $BR.'install user table = start';

    global $DB;

    $res = $DB->createTable(
        'users',
        array( 
            'id int PRIMARY KEY AUTO_INCREMENT',
            'login varchar(50)',
            'name varchar(50)',
            'pass varchar(50)',
            'hash_auth varchar(50)',
            //'groups int'
        )
    );

   // if ($res === true){
        echo $BR.'install user table = OK!';

        echo $BR.'add admin user (and test user) = start';

        $res = $DB->insertDb(
            'users',
            array(
                'login' => 'admin',
                'name' => 'admin',
                'pass' =>   'admin',
                //'groups' => 1
            )
        );

        $res = $DB->insertDb(
            'users',
            array(
                'login' => 'asd',
                'name' => 'asd',
                'pass' =>  'asdasd',
                //'groups' => 2
            )
        );

//        if($res === true){
            echo $BR.'add admin user = OK!';
//        }else{
//            echo $BR.'add admin user = ERROR!';
//        }
//    }else{
//        echo $BR.'install user table = ERROR!';
//    }

    // задать группы прав доступа и действия разрешаемые для пользователей групп
    echo $BR.'install access users = start';
    
    // это действия пользователей к которые можно указать для группы пользователей
    //  суда нужно добавлять новые при появление нового в админке и прочего (модули если будут сделаны в этой версии)
    $res = $DB->createTable(
        'action_user',
        array(
            'id int PRIMARY KEY AUTO_INCREMENT',
            'code varchar(255)',
            'text varchar(255)',
        )
    );


    $DB->insertDb(
        'action_user',
        array(
            'code' => 'show_admin_panel',
            'text' => 'Просматривать админку | View admin panel',
        )
    );

    $DB->insertDb(
        'action_user',
        array(
            'code' => 'action_all',
            'text' => 'Редактировать всё (Админ) | Edit All (Admin)',
        )
    );

    $DB->insertDb(
        'action_user',
        array(
            'code' => 'edit_users',
            'text' => 'Изменение пользователей (кроме админов) | Edit users (except admins)',
        )
    );


    echo $BR.'install access users = OK!';

    echo $BR.'install user groups (add action user) = start';
    // это группы (пользователей) прав доступа
    $res = $DB->createTable(
        'access_group',
        array(
            'id int PRIMARY KEY AUTO_INCREMENT',
            'code varchar(255)',
            'name varchar(255)',
            'text varchar(255)',
            'code_action_user varchar(255)' // код действия пользователя, разрешённый для данной группы
        )
    );

    $DB->insertDb(
        'access_group',
        array(
            'code' => 'admins',
            'name' => 'Админы | Admins',
            'text' => 'Админы, есть права на всё | Admins, have rights to everything',
            'code_action_user' => 'action_all'
        )
    );
    $DB->insertDb(
        'access_group',
        array(
            'code' => 'admins',
            'name' => 'Админы | Admins',
            'text' => 'Админы, есть права на всё | Admins, have rights to everything',
            'code_action_user' => 'show_admin_panel'
        )
    );

    $DB->insertDb(
        'access_group',
        array(
            'code' => 'content',
            'name' => 'Контент | Content',
            'text' => 'Те кто изменяют контент сайта | Those who change the content of the site',
            'code_action_user' => 'show_admin_panel'
        )
    );

    $DB->insertDb(
        'access_group',
        array(
            'code' => 'user_admin',
            'name' => 'Админи по пользователям | Admin for users',
            'text' => 'Могут изменять только пользователей | Can change users',
            'code_action_user' => 'edit_users'
        )
    );
    $DB->insertDb(
        'access_group',
        array(
            'code' => 'user_admin',
            'name' => 'Админи по пользователям | Admin for users',
            'text' => 'Могут изменять только пользователей | Can change users',
            'code_action_user' => 'show_admin_panel'
        )
    );

    echo $BR.'install user groups = OK!';

    echo $BR.'add users in user groups = start';
    // в этой таблице будут группы и относящиеся к ним пользователи
    $res = $DB->createTable(
        'users_in_groups',
        array(
            'id int PRIMARY KEY AUTO_INCREMENT',
            'code_group varchar(255)',
            'id_user int',
        )
    );



    $DB->insertDb(
        'users_in_groups',
        array(
            'code_group' => 'admins',
            'id_user' => 1,
        )
    );

    $DB->insertDb(
        'users_in_groups',
        array(
            'code_group' => 'user_admin',
            'id_user' => 2,
        )
    );

    echo $BR.'add users in user groups = OK';

    // общие свойства для пользователей
    echo $BR.'install all users propertys = start';
    // таблица с общими свойствами (список общих свойств для всех пользователей)
    $res = $DB->createTable(
        'create_all_users_property',
        array(
            'id int PRIMARY KEY AUTO_INCREMENT',
            'name_property varchar(255)',
            'type_property int',
            'code varchar(255)',
        )
    );

    // типы общих свойств для пользователей
    $res = $DB->createTable(
        'type_all_user_propertys',
        array(
            'id int PRIMARY KEY AUTO_INCREMENT',
            'name_type varchar(255)',
            'info varchar(255)',
            'code varchar(255)',
        )
    );

    $DB->insertDb(
        'type_all_user_propertys',
        array(
            'name_type' => 'text',
            'info' => 'input type text',
            'code' => 'text',
        )
    );

    // значения общего свойства типа текст
    $res = $DB->createTable(
        'value_all_user_propertys_text',
        array(
            'id int PRIMARY KEY AUTO_INCREMENT',
            'value varchar(255)',
            'id_users int',
            'id_property int',
        )
    );

    echo $BR.'install all users propertys = OK';

    echo $BR.'install all modules db = start';

    // теперь установка частей БД относящихся к модулям
    $module = Gy\Core\ModuleManager::getInstance();
    $module->installBdAllModules();

    echo $BR.'install all modules db = OK!';

    echo $BR.'-----install gy core taldes db = OK!-----'.$BR;

} else {
    echo '! Error. You need to run the script in the console';

}
