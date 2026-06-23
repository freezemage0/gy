<?php
include "../../gy/gy.php"; // подключить ядро // include core

global $USER;
$module = Gy\Core\ModuleManager::getInstance();
global $APP;

$data  = $_GET;

if (Gy\Core\User\AccessUserGroup::accessThisUserByAction( 'show_admin_panel')
    && !empty($data['page']) 
) {

    // надо ссылаться сюда для получения страницы админки относящихся к модулям,
    // пример в урле /gy/admin/container-data-edit.php станет 
    //   /gy/admin/get-admin-page.php?page=container-data-edit ... 
    //   далее как и было
    // + подумать над безопасностью
    // если есть такая страница то подключить её
    if (!empty($module->nameModuleByNameAdminPage[$data['page']])) {
        include_once( $APP->url.'/modules/'.$module->nameModuleByNameAdminPage[$data['page']].'/admin/'.$data['page'].'.php' );
    }

} else {
    header( 'Location: /gy/admin/' );
}
