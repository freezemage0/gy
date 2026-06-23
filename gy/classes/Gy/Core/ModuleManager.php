<?php

declare(strict_types=1);

namespace Gy\Core;

use const DIRECTORY_SEPARATOR;

/**
 * Module - работа с модулями фреймворка
 */
class ModuleManager
{
    // массив подключённых модулей
    private array $arrayIncludeModules = [];

    // массив подключённых модулей и их версии
    public array $arrayIncludeModulesAndVersion = [];

    // соответствие компонентов подключенным модулям
    public array $nameModuleByComponentName = [];

    // соответствие имени класса (находящегося в модуле) и имени модуля
    public array $nameClassModuleByNameModule = [];

    // связь имени страницы и модуля
    public array $nameModuleByNameAdminPage = [];

    // пункты меню для админ панели (связанные с модулями)
    public array $buttonMenuAdminPanel = [];

    // условия показа пунктов меню админки для подключённых модулей
    public array $isShowButtonsMenuAdminPanelModules = [];

    // url до папки gy в проекте
    private ?string $urlGyCore = null;

    /**
     * @deprecated Use ServiceLocator::getInstance()->getModuleManager() instead.
     *
     * getInstance
     *  - получение объекта класса (всегда один обьект)
     * реализация singleton
     *
     * @return jbject this class
     */
    public static function getInstance(): ModuleManager
    {
        return Container::getInstance()->getModuleManager();
    }

    public function setUrlGyCore($urlGyCore): void
    {
        $this->urlGyCore = $urlGyCore;
    }

    /**
     * IncludeModule
     *  - подключить указанный модуль
     *  (т.е. ядро узнает о классах, компонентах модуля и прочем)
     *
     * @param string $name - имя модуля
     *
     * @return bool - вернёт true если модуль найден и подключен или false если нет
     */
    public function includeModule(string $name): bool
    {
        if ($this->urlGyCore === null) {
            return false;
        }

        return $this->includeModuleByUrl($this->urlGyCore . '/modules/' . $name . '/');
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

        if (!\is_file($urlModule . 'init.php')) {
            return false;
        }

        include $urlModule . 'init.php';

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

        return true;
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
            $result = $this->arrayIncludeModules[$this->nameModuleByComponentName[$nameComponent]] . 'component/' . $nameComponent;
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
            $result = $this->arrayIncludeModules[$this->nameClassModuleByNameModule[$nameClass]] . 'classes/' . $nameClass . '.php';
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
        $result = [];
        if ($handleDirs = opendir($this->urlGyCore . '/modules/')) {
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
    public function includeAllModules(): void
    {
        foreach ($this->searchAllModules() as $value) {
            $this->includeModule($value);
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

        if (file_exists($this->urlGyCore . '/modules/' . $nameModule . '/install/installDataBaseTable.php')) {
            include_once($this->urlGyCore . '/modules/' . $nameModule . '/install/installDataBaseTable.php');
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

    public function autoloadModuleClass(string $fqn): void
    {
        $config = Container::getInstance()->getConfiguration();
        //   1. для модулей завести пространство имён типа Gy\Modules\<имя модуля>\Classes\<имя класса>
        //   2. потом подключать вначале customDir/vendor
        //   3. уже потом из раздела gy/classes

        // из пространства имён составляю путь к классу
        $className = \ltrim($fqn, '\\');

        $fileName = '';
        $namespace = '';
        if ($lastNsPos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }

        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        // определяю является ли путь и вызываемый класс, классом модуля,
        //   если так то подключаю класс модуля
        //   пространство имён будет: Gy\Modules\<имя модуля>\Classes\<имя класса>

        // условие регулярки для такого пространства имён
        $separator = \preg_quote(DIRECTORY_SEPARATOR);
        $pattern = "#^(Gy{$separator}Modules{$separator})(.*)({$separator}Classes{$separator})(.*).php#";

        $matches = []; // тут результат парсинга

        if (\preg_match($pattern, $fileName, $matches) !== 0) {
            //$parseUrl[2] - тут имя модуля
            //$parseUrl[4] - Тут имя класса

            // TODO можно было бы подключить конкретный модуль но пока оставлю старую механику
            //   (когда подключаются все сразу)

            // проверю есть ли класс в подключённых модулях и подключу (в модулях psr0 нет)
            $meyByClassModule = $this->getUrlModuleClassByNameClass($matches[4]);
            if ($meyByClassModule !== false) {
                require_once($meyByClassModule);
            }

            return;
        }

        $customClass = $config->projectRoot . DIRECTORY_SEPARATOR . 'customDir' . DIRECTORY_SEPARATOR . 'classes/' . DIRECTORY_SEPARATOR . $fileName;
        if (\is_file($customClass)) {
            // иначе, если не класс модуля, ищу класс в разделе для кастомных (пользовательских) классов
            require_once $customClass;

            return;
        }

        $gyClass = $config->projectRoot . DIRECTORY_SEPARATOR . 'gy/classes' . DIRECTORY_SEPARATOR . $fileName;
        if (\is_file($gyClass)) {
            // иначе ищу класс в классах gy
            require_once $gyClass;
        }
    }
}
