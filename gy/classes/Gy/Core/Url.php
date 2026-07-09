<?php

namespace Gy\Core;

/**
 * Url - класс с url текущего запроса
 * class work url
 */
class Url
{
    /**
     * getThisUrlNotGetProperty 
     *  - получить текущий url без параметров get
     * 
     * @return null/string 
     */
    static public function getThisUrlNotGetProperty(): ?string {
        $result = null;

        $url = \explode('?', $_SERVER['REQUEST_URI']);
        if (!empty($url[0])){
            $result = $url[0];
        }

        return $result;
    }
}
