<?php

namespace Gy\Core;

if (!defined("GY_CORE") && (GY_CORE !== true)) die( "gy: err include core" );

final class Crypto
{
    private string $salt;

    /**
     * setSalt - установить соль (некая строка)
     * @param string $salt
     */
    public function setSalt(string $salt): void
    {
        $this->salt = $salt;
    }

    /**
     * getSalt - получить значение соли
     * @return string
     */
    public function getSalt(): string
    {
        return $this->salt;
    }

    /**
     * FIXME: Криптографически небезопасный генератор случайных строк. Заменить на более безопасный алгоритм.
     * getRandString - даст произвольную строку
     * @return string
     */
    public function getRandString(): string
    {
        return md5(microtime().$this->salt);
    }

    /**
     * FIXME: Выходит за пределы ответственности класса. Подобная функция должна быть в другом месте, например, в менеджере сессии.
     * TODO: Создать менеджер сессии.
     *
     * getStringForUserCookie - даст строку для пользовательской куки
     *  (склеит соль имя id пользователя и сделает md5)
     * @param string $login
     * @param string $name
     * @param int $id
     * @return string (md5)
     */
    public function getStringForUserCookie($login, $name, $id)
    {
        return md5(microtime().$login.$this->salt.$name.$id);
    }
}


