<?php

use Gy\Core\Container;

if (!defined("GY_CORE") && (GY_CORE !== true)) die( "gy: err include core" );

$data  = $_REQUEST;

// подключить модель (файл php model этого компонента) // include model this component
if (isset($this->model)) {
    $this->model->includeModel();
}

$this->model->backUrl = $this->arParam['back-url'];

$redirectUrl = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);

// взять все группы пользователей
$this->model->allUsersGroups = Gy\Core\User\AccessUserGroup::getAccessGroup();

function checkProperty($arr, $userProperty, $allUsersGroups){
    $result = true;
    foreach ($userProperty as $val) {
        if (empty($arr[$val])) {
            $result = false;
        }
    }

    if ($result) {
        foreach ($arr['groups'] as $value) {  // TODO протестировать

            if (empty($allUsersGroups[$value])) {
                $result = false;
            }

            if (!empty($arr['groups']['admins']) && !$USER->isAdmin()) { // TODO протестировать
                $result = false;
            }
        }
    }

    return $result;
}

if (!empty($data[$this->lang->getMessage('button')]) && ($data[$this->lang->getMessage('button')] == $this->lang->getMessage('button'))) {
    if (checkProperty($data, $this->model->userProperty, $this->model->allUsersGroups)) {
        // добавление пользователя
        global $USER;
        $arDaraUser = array();
        foreach ($this->model->userProperty as $val) {
            $arDaraUser[$val] = $data[$val];
        }

        // убрать группы из добавления
        unset($arDaraUser['groups']);

        if ($USER->addUsers($arDaraUser)) {
            // найти id добавленного пользователя
            global $DB;
            $cryptoService = Container::getInstance()->getCryptoService();
            $res = $DB->selectDb(
                $USER->tableName,
                array('*'),
                array(
                    'AND' => array(
                        array('=' => array('login', "'".$arDaraUser['login']."'")),
                        array('=' => array('pass', "'".md5($arDaraUser['pass'].$cryptoService->getSalt())."'") )
                    )
                )
            );
            $dataAddNewUser = $DB->fetch($res);

            // добавить пользователя к указанным группам
            Gy\Core\User\AccessUserGroup::deleteUserInAllGroups($dataAddNewUser['id']);
            foreach ($data['groups'] as $value) {
                Gy\Core\User\AccessUserGroup::addUserInGroup($dataAddNewUser['id'], $value);
            }

            $this->model->stat = 'ok';
        } else {
            $this->model->stat = 'err';
        }

    } else {
        $this->model->statText = $this->lang->getMessage('err_property') ; // ! Не все поля заполнены
        $this->model->stat = 'err';
    }

} elseif ((!empty($this->model->stat) && ($this->model->stat != 'err')) || empty($this->model->stat)) {
    $this->model->stat = 'add';
}

if (empty($data['stat'])) {
    header( 'Location: '.$redirectUrl.'?stat='.$this->model->stat );
} else {
    $this->model->stat = $data['stat'];
}

// установить модель этого компонента в шаблон (view)
$this->template->setModel($this->model);

// показать шаблон (view)
$this->template->show();
