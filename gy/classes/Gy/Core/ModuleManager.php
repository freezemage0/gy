<?php
declare(strict_types=1);

namespace Gy\Core;

/**
 * Module - работа с модулями фреймворка
 */
class ModuleManager
{
    // массив подключённых модулей
    private $arrayIncludeModules = array();

    // массив подключённых модулей и их версии
    public $arrayIncludeModulesAndVersion = array();

    // соответствие компонентов подключенным модулям
    public $nameModuleByComponentName = array();

    // соответствие имени класса (находящегося в модуле) и имени модуля
    public $nameClassModuleByNameModule = array();

    // связь имени страницы и модуля
    public $nameModuleByNameAdminPage = array();

    // пункты меню для админ панели (связанные с модулями)
    public $buttonMenuAdminPanel = array();

    // условия показа пунктов меню админки для подключённых модулей
    public $isShowButtonsMenuAdminPanelModules = array();

    // url до папки gy в проекте
    private $urlGyCore = false;

    // объект класса (всегда будет один)
    private static $module;

    private function  __construct()
    {
        // заполнить пустотой
        $this->arrayIncludeModulesAndVersion = array();
        $this->arrayIncludeModules = array();
        $this->nameModuleByComponentName = array();
        $this->nameClassModuleByNameModule = array();
    }

    /**
     * getInstance 
     *  - получение объекта класса (всегда один обьект)
     * реализация singleton
     * 
     * @return jbject this class
     */
    public static function getInstance(): ModuleManager
    {
        return self::$module ??= new static();
    }

    public function setUrlGyCore($urlGyCore)
    {
        $this->urlGyCore = $urlGyCore;
    }

    /**
     * IncludeModule 
     *  - подключить указанный модуль
     *  (т.е. ядро узнает о классах, компонентах модуля и прочем)
     * 
     * @param string $nameModule - имя модуля
     * 
     * @return bool - вернёт true если модуль найден и подключен или false если нет
     */
    public function includeModule($nameModule)
    {
        $result = false;
        if ($this->urlGyCore !== false) {
            $result = $this->IncludeModuleByUrl($this->urlGyCore.'/modules/'.$nameModule.'/');
        } // TODO возможно кудато вывести ошибку
        
        return $result;
    }

    /**
     * includeModuleByUrl
     *  - подключить модуль по указанному урлу 
     * 
     * @param string $urlModule
     * @return boolean
     */
    public function includeModuleByUrl($urlModule)
    { // TODO можно добавить проверки на ошибки 
        $result = false;

        if (file_exists($urlModule.'init.php' )) {
            include $urlModule.'init.php';

            // тут имя модуля
            if (!empty($nameThisModule)) {
                $this->arrayIncludeModules[$nameThisModule] = $urlModule;
                //unset($nameThisModule);

                if (!empty($versionThisModule)) {
                    $this->arrayIncludeModulesAndVersion[$nameThisModule] = $versionThisModule;
                    unset($versionThisModule);
                }

            }

            // тут список компонентов модуля
            if (!empty($componentsThisModule)) {

                foreach ($componentsThisModule as $value) {
                    $this->nameModuleByComponentName[$value] = $nameThisModule;
                }

                unset($componentsThisModule);
            }

            // тут список классов модуля
            if (!empty($classesThisModule)) {

                foreach ($classesThisModule as $value) {
                    $this->nameClassModuleByNameModule[$value] = $nameThisModule;
                }
                unset($classesThisModule);
            }

            // тут список страниц админки
            if (!empty($adminPageThisModule)) {

                foreach ($adminPageThisModule as $value) {
                    $this->nameModuleByNameAdminPage[$value] = $nameThisModule;
                }
                unset($adminPageThisModule);
            }

            // пункты меню в админке
            if (!empty($pagesFromAdminMenu)) {
                $this->buttonMenuAdminPanel[$nameThisModule] = $pagesFromAdminMenu;
                unset($pagesFromAdminMenu);
            }

            // условия показа пунктов меню админки для подключённых модулей
            if (!empty($isShowButtonsMenuAdminPanetThisModule)) {
                $this->isShowButtonsMenuAdminPanelModules[$nameThisModule] = $isShowButtonsMenuAdminPanetThisModule;
                unset($isShowButtonsMenuAdminPanetThisModule);
            }
        }
        return $result;
    }


    /**
     * getModulesComponent
     *  - получить по имени компонента данные о компоненте из подключённых модулей
     * 
     * @param string $nameComponent
     * @return string
     */
    public function getModulesComponent($nameComponent)
    {
        $result = false;

        if (!empty($this->nameModuleByComponentName[$nameComponent])) {
            $result = $this->arrayIncludeModules[ $this->nameModuleByComponentName[$nameComponent] ].'component/'.$nameComponent;
        }

        return $result;
    } 

    /**
     * getUrlModuleClassByNameClass
     *  - по имени класса, если он есть в одном из подключённых модулей выдать урл на класс
     * 
     * @param string $nameClass
     * @return string
     */
    public function getUrlModuleClassByNameClass($nameClass)
    {
        $result = false;
        if (!empty($this->nameClassModuleByNameModule[$nameClass])) {
            $result = $this->arrayIncludeModules[ $this->nameClassModuleByNameModule[$nameClass] ].'classes/'.$nameClass.'.php';
        }
        return $result;
    }

    /**
     * searchAllModules()
     *  - найти все разделы из раздела /gy/modules , т.е. все имеющиеся модули
     * 
     * @return array
     */
    public function searchAllModules()
    {
        $result = array();
        if ($handleDirs = opendir( $this->urlGyCore.'/modules/' )) {
            while (false !== ($dirName = readdir($handleDirs))) {
                if (($dirName != '.') && ($dirName != '..')) {
                    $result[$dirName] = $dirName;
                }
            }
            closedir($handleDirs);
        }
        return $result;
    }

    /**
     * includeAllModules()
     *  - подключить все имеющиеся модули
     * 
     */
    public function includeAllModules()
    {
        $allModules = $this->searchAllModules();
        if (!empty($allModules)) {
            foreach ($allModules as $value) {
                $this->includeModule($value);
            }
        }
    }

    /**
     * installDbModuleByNameModule 
     *  - установить часть БД связанную с этим модулем
     * 
     * @param string $nameModule - имя модуля
     * @return boolean
     */
    public function installDbModuleByNameModule($nameModule)
    { // TODO пока только установка для mysql
        $result = false;
        
        if (file_exists($this->urlGyCore.'/modules/'.$nameModule.'/install/installDataBaseTable.php' )) {
            include_once( $this->urlGyCore.'/modules/'.$nameModule.'/install/installDataBaseTable.php' );
            $result = true;
        }

        return $result;
    }

    /**
     * installBdAllModules 
     *  - установить части БД для всех модулей
     */
    public function installBdAllModules()
    {
        $allModules = $this->searchAllModules();
        if (!empty($allModules)) {
            foreach ($allModules as $value) {
                $this->installDbModuleByNameModule($value);
            }
        }
    }

    /**
     * getButtonsMenuByModule
     *  - вернуть кнопки меню панели администратора определённые в указанном модуле
     * 
     * @param string $nameModule - код модуля
     * @return array - массив с кнопками где ключ это название пункта меню а значение url
     */
    public function getButtonsMenuByModule($nameModule)
    {
        return $this->buttonMenuAdminPanel[$nameModule];
    }

    /**
     * getButtonsMenuAllModules
     *  - вернуть все пункты меню админки всех подключённых модулей
     * 
     * @return array - массив с кнопками где ключ это код модуля,
     *   а значения как результат getButtonsMenuByModule
     */
    public function getButtonsMenuAllModules()
    {
        return $this->buttonMenuAdminPanel;
    }

    /**
     * getFlagShowButtonsAdminPanelByModule
     *  - вернуть условие показа кнопок в админке,
     *  это код для метода Gy\Core\User\AccessUserGroup::accessThisUserByAction
     *  т.е. действие и если оно разрешено пользователю то покажется пункты меню в админке
     *  
     * 
     * @param string $nameModule - код модуля
     * @return string - код действия 
     */
    public function getFlagShowButtonsAdminPanelByModule($nameModule)
    {
        return $this->isShowButtonsMenuAdminPanelModules[$nameModule];
    }

    public function getInfoAllIncludeModules()
    {
        return $this->arrayIncludeModulesAndVersion;
    }

}
