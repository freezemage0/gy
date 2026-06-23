<?php

declare(strict_types=1);

namespace Gy\Core;

use Gy\Core\Component\Component;

final class App
{
    public string $coreDirectory;
    // настройки проекта
    public Localization $lang;       // табличка с языковыми сообщениями
    //public $db; // db
    public $urlProject; // урл как $this-url только без /gy в конце

    public function __construct(
        public readonly Configuration $configuration,
    ) {
        // записать ещё путь c /gy
        $this->coreDirectory = $this->configuration->projectRoot . '/gy';

        // если есть языковой файл то надо подключить его
        $this->lang = new Localization($this->coreDirectory, 'app', $this->configuration->language);
    }

    /**
     *  component отобразить компонент // show component
     *
     * @param string $name - имя компонента и контроллера сразу
     * @param string $template - имя шаблона
     * @param array $parameters - параметры компонента (параметры кеша и прочие нюансы)
     *      // array component config
     *  вернёт объект компонент
     *
     * TODO возможно понадобится сделать подключение модели
     *     // если делать универсальные модели для компонентов
     *  или возможность подключать много моделей разных
     *  maybe includ many model in component
     */
    public function component(string $name, string $template, array $parameters): Component
    {
        if ($name !== 'includeHtml') {
            // обезопасим входные параметры
            $parameters = Security::filterInputData($parameters);
        }

        return new Component(
            name: $name,
            template: $template,
            arParam: $parameters,
            url: $this->urlProject,
            lang: $this->configuration['lang'],
        );
    }

    /**
     * getAllUrlTisPage()
     *  - вернёт полный путь к текущей страницы (вместе с get параметрами)
     *
     * @return string
     */
    public function getAllUrlTisPage(): string
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * getUrlTisPageNotGetProperty()
     *  - вернёт полный путь к текущей страницы (без get параметров)
     *
     * @return string
     */
    public function getUrlTisPageNotGetProperty(): string
    {
        return $_SERVER['SCRIPT_NAME'];
    }
}
