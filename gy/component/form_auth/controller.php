<?php 
if (!defined("GY_CORE") && (GY_CORE !== true)) die( "gy: err include core" );

use Gy\Core\Captcha;
//use Gy\Core\User\AccessUserGroup;

// контроллер компонента form_auth (форма авторизации)

// подключить модель // include model this component
if (isset($this->model)) {
    $this->model->includeModel();
}

if (!empty($_REQUEST['secretKeyAdminPanel'])) {
    $urlKey = '&secretKeyAdminPanel='.$_REQUEST['secretKeyAdminPanel'];
}

// были доступны параметры
//echo '$arParam<pre>'; print_r($this->arParam); echo '</pre>';

// если задан параметр idComponent значит сверить с пришедшим
$isChackIdComponent = ( empty($this->arParam['idComponent'])
    || (!empty($this->arParam['idComponent']) && !empty($_REQUEST['idComponent']) && ($this->arParam['idComponent'] == $_REQUEST['idComponent']) )
);

$isShowAdminPanel = false;

if (!empty($_REQUEST['auth'])) {
    $thisLogin = $_REQUEST['auth'];
}

global $USER;

$isShowAdminPanel = Gy\Core\User\AccessUserGroup::accessThisUserByAction( 'show_admin_panel');

$redirectUrl = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);

if ($isShowAdminPanel === true){

    $thisLogin = $USER->getDataThisUser()['name'];
    $arRes["auth_ok"] = 'ok';
    $arRes["auth_user"] = $thisLogin;

} elseif (!empty($_REQUEST['auth']) && !empty($_REQUEST['pass']) && !empty($_REQUEST['capcha'])) {

    $capcha = new Captcha($APP->url.Captcha::$defaultUrlFonts);
    
    if ($capcha->validate($_REQUEST['capcha'])) {

        $USER->authorized($_REQUEST['auth'], $_REQUEST['pass']);
        $isShowAdminPanel = Gy\Core\User\AccessUserGroup::accessThisUserByAction( 'show_admin_panel');

        if ($isShowAdminPanel === false) {
            $arRes["err"] = 'err1';
        }

        if ($isChackIdComponent && $isShowAdminPanel) {
            $arRes["auth_ok"] = 'ok';
            $arRes["auth_user"] = $thisLogin;

            header( 'Location: '.$redirectUrl.$urlKey );
        } else {
            $arRes['form_input']["auth"] = "auth";
            $arRes['form_input']["pass"] = "pass";
            header( 'Location: '.$redirectUrl.'?err=err1'.$urlKey );

        }
    } else {
        $arRes['form_input']["auth"] = "auth";
        $arRes['form_input']["pass"] = "pass";
        header( 'Location: '.$redirectUrl.'?err=err_capcha'.$urlKey );
    }
} else {
    if (!empty($_REQUEST['err'])) {
        $arRes["err"] = $_REQUEST['err'];
    }
    $arRes['form_input']["auth"] = "auth";
    $arRes['form_input']["pass"] = "pass";
}

if (!empty($arRes["auth_ok"]) && ($arRes["auth_ok"] == 'ok') && !empty($_REQUEST[ $this->lang->getMessage('button-exit') ])) {
    if ($USER->userExit()) {
        header( 'Location: '.$redirectUrl.$urlKey );
    }
}

// показать шаблон
$this->template->show($arRes, $this->arParam);
