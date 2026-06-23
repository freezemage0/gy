<?php
namespace Gy\Core\Component\Mvc;

class Model
{
    public $url; // ссылка на шаблон

    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * includeModel - подключить файл с моделью компонента
     */
    public function includeModel()
    {		
        require_once $this->url; // !!!
    }
}

