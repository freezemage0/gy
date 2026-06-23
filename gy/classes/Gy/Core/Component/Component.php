<?php 

namespace Gy\Core\Component;

use Gy\Core\Localization;
use Gy\Core\ModuleManager;
use Gy\Core\Component\Mvc\Controller;
use Gy\Core\Component\Mvc\Model;
use Gy\Core\Component\Mvc\Template;

if (!defined("GY_CORE") && (GY_CORE !== true)) die( "gy: err include core" );

class Component
{

    public $template; // тут будут объект класса template
    public $controller;
    public $model;
    public $url;
    public $lang; 

    public function __construct( $name, $template, $arParam, $url, $lang )
    {
        $this->lang = new Localization($url.'/gy/lang/', 'component', $lang);

        // TODO $template - сюда можно и пустую строку записать
        // могут быть разные шаблоны

        $err = 0;
        $errText = '';

        // нужно попробовать найти подключаемый компонент среди подключённых модулей
        $module = ModuleManager::getInstance();
        $urlComponentInModule = $module->getModulesComponent($name);

        if (($err == 0) && file_exists($url.'/customDir/component/'.$name.'/teplates/'.$template.'/template.php' )) {
            // если есть такой компонент и указанный шаблон в папке /customDir/ то подключить от туда
            $template = new Template($url.'/customDir/component/'.$name.'/teplates/'.$template, $lang ); 
        } elseif (($urlComponentInModule !== false) && file_exists($urlComponentInModule.'/teplates/'.$template.'/template.php' )) {
            // проверить нет ли компонента среди подключенных модулей
            $template = new Template($urlComponentInModule.'/teplates/'.$template, $lang ); 
        } elseif (($err == 0) && file_exists($url.'/gy/component/'.$name.'/teplates/'.$template.'/template.php' )) { 
            // если нет то поискать шаблон в стандартной папке с компонентами
            $template = new Template($url.'/gy/component/'.$name.'/teplates/'.$template, $lang );
        } else {
            $err = 1;
            $errText = $this->lang->getMessage('err_not_controller');
        }

        if (($err == 0) && file_exists($url.'/customDir/component/'.$name.'/controller.php' )) { 
            $this->controller = new Controller($url.'/customDir/component/'.$name, $lang); // всегда один
        } elseif (($urlComponentInModule !== false) && file_exists($urlComponentInModule.'/controller.php' )) {
            $this->controller = new Controller($urlComponentInModule, $lang);
        } elseif (($err == 0) && file_exists($url.'/gy/component/'.$name.'/controller.php' )) {
            $this->controller = new Controller($url.'/gy/component/'.$name, $lang); // всегда один
        } else {
            $err = 2;
            $errText = $this->lang->getMessage('err_not_controller') ;
        }

        if (($err == 0) && file_exists($url.'/customDir/component/'.$name.'/model.php' )) {
            $model = new Model($url.'/customDir/component/'.$name.'/model.php'); // может и не быть
            $this->controller->setModel($model);
        } elseif (($urlComponentInModule !== false) && file_exists($urlComponentInModule.'/model.php' )) {
            $model = new Model($urlComponentInModule.'/model.php'); // может и не быть
            $this->controller->setModel($model);
        } elseif (($err == 0) && file_exists($url.'/gy/component/'.$name.'/model.php' )) {
            $model = new Model($url.'/gy/component/'.$name.'/model.php'); // может и не быть
            $this->controller->setModel($model);
        } 

        // TODO вывести ошибку если что то не найдено // значит файлы не все есть

        if ($err != 0) { // если есть ошибки
            $this->ShowErr($errText);
        } else { // иначе запускаем компонент

            $this->controller->setTemplate($template); // задать шаблон
            $this->controller->setArParam($arParam); // передать параметры компонента // set array property component

            $this->run();
        }

        $this->url = $url.'/gy';
    }

    /**
     * run() 
     */
    public function run()
    {
        $this->controller->run();
        //$this->template->show($arRes);
    }

    /**
     * ShowErr 
     * @param type $err
     */
    public function ShowErr($err)
    { // TODO вынести в отдельный класс про ошибки
        echo '<div class=gy_err>'.$err.'</div>';
    }

}
