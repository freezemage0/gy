<?php 

namespace Gy\Core\AbstractClasses;

if (!defined("GY_CORE") && (GY_CORE !== true)) die( "gy: err include core" );

/** 
 * abstract class Cache - описывает класс работы с кешем
 */
abstract class Cache
{

    /**
     * cacheInit - инициализация кеша
     * @param string $key
     * @param int $expiresIn - время кеширования в секундах
     * @return boolean
     */
    abstract public function cacheInit(string $key, int $expiresIn);
    
    /**
     * getCacheData() - получить данные из кеша
     * @return mixed - может быть массив или одиночное значение любого типа
     */
    abstract public function getData();
    
    /**
     * setCacheData - записать данные в в кеш
     * @param mixed $data - может быть массив или одиночное значение
     */
    abstract public function setData(mixed $data): void;

    abstract public function clear(): void;
}
