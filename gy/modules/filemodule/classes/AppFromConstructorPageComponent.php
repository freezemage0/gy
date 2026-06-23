<?php

namespace Gy\Modules\filemodule\Classes;

use Gy\Core\ModuleManager;

if (!defined("GY_CORE") && (GY_CORE !== true)) die( "gy: err include core" );

/**
 * class AppFromConstructorPageComponent подменит собой обьект app 
 *  что бы подловить подключаемые компоненты
 */

class AppFromConstructorPageComponent
{

    private $allDateIncludeComponents = array();
    private $intKey = 0;
    public $urlProject;
    public $options; // настройки проекта 

    public function __construct($urlProject, $options)
    {
        $this->urlProject = $urlProject;
        $this->options = $options;
    }

    /**
     * getInfoAboutComponent
     *  - получить информацию о компоненте если есть файл componentInfo.php
     * 
     * @param string $name
     * @param string $template
     * @param array $arParam
     * @param string $url
     * @return array
     */
    public function getInfoAboutComponent( $name, $template, $arParam, $url )
    {
        // нужно попробовать найти подключаемый компонент среди подключённых модулей
        $module = ModuleManager::getInstance();
        $urlComponentInModule = $module->getModulesComponent($name);
        $componentInfo = array();

        if (file_exists($url.'/customDir/component/'.$name.'/componentInfo.php')) {
            require $url.'/customDir/component/'.$name.'/componentInfo.php';
        } elseif (($urlComponentInModule !== false) && file_exists($urlComponentInModule.'/componentInfo.php' )) {
            require $urlComponentInModule.'/componentInfo.php'; // может и не быть
        } elseif (file_exists($url.'/gy/component/'.$name.'/componentInfo.php')) {
            require $url.'/gy/component/'.$name.'/componentInfo.php'; // может и не быть
        }

        return $componentInfo;
    }

    /**
     * component 
     *  - метод подключения компонента, а в нашем классе возьмёт просто информацию
     *    о подключаемом компоненте
     * 
     * @global type $APP
     * @param type $name
     * @param type $template
     * @param type $arParam
     */
    public function component($name, $template, $arParam  )
    {
        global $APP;
        $this->allDateIncludeComponents[$this->intKey] = array(
            'name' => $name,
            'template' => $template,
            'arParam' => $arParam,
            'componentInfo' => self::getInfoAboutComponent( $name, $template, $arParam, $this->urlProject)
        );

        $this->intKey++;
    }

    /**
     * getAllDataIncludeComponents
     *  - получить данные по всем подключенным компонентам
     *
     * @return array
     */
    public function getAllDataIncludeComponents()
    {
        return $this->allDateIncludeComponents;
    }

    /**
     * getCodeIncludeComponent
     *  - сделать php код вызова коппонента из переданных параметров
     * 
     * @param string $componentName
     * @param string $templateName
     * @param array $arParams
     * @return string
     */
    public static function getCodeIncludeComponent($componentName, $templateName, $arParams)
    {

        $codeIncludeComponent = "\n".'$APP->component('."\n";
        $codeIncludeComponent .= "   '".$componentName."',"."\n";
        $codeIncludeComponent .= "   '".$templateName."',"."\n";
        $codeIncludeComponent .= '   array('."\n";
        if (!empty($arParams)) {
            foreach ($arParams as $key => $value) {
                if (!is_numeric($value)) {
                    $codeIncludeComponent .= "     '".$key."' => '".$value."',"."\n";
                } else {
                    $codeIncludeComponent .= "     '".$key."' => ".$value.",\n";
                }
            }
        }
        $codeIncludeComponent .= '   )'."\n";

        $codeIncludeComponent .= ');'."\n";

        return $codeIncludeComponent;
    }

}
