<?php 
namespace Gy\Core\Component\Mvc;

use Gy\Core\Localization;

class Controller
{
    public $model;
    public $controller; // ссылка для запуска выбранного контроллера компонента
    public $lang;
    public $template; // объект шаблона
    public $arParam;

    public function __construct($url, $lang)
    {
        $this->controller = $url.'/controller.php';
        $this->lang = new Localization($url, 'controller', $lang);
    }

    /**
     * setModel
     * @param type $model
     */
    public function setModel($model)
    { // установить ссылку на модель если есть
        $this->model = $model;
    }

    /**
     * setTemplate - задать шаблон
     * @param object class template $template
     */
    public function setTemplate($template)
    {  
        $this->template = $template;	
    }

    /**
     * setArParam - задать параметры компонента // set array property component
     * @param type $arParam
     */
    public function setArParam($arParam)
    { 
        $this->arParam = $arParam;
    }

    /**
     * run 
     */
    public function run()
    {
        $arRes = array();
        include $this->controller;
    }

}
