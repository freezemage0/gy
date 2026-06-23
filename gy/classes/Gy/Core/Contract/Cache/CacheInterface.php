<?php

declare(strict_types=1);



namespace Gy\Core\Contract\Cache;


/**
 * cache - класс для работы с кешем
 * для даботы нужен раздел gy/cache/
 */
interface CacheInterface
{
    /**
     * cacheInit - инициализация кеша, надо проверить есть кеш по заданным параметрам
     * @param string $key
     * @param int $expiresIn - время кеширования в секундах
     * @return bool
     */
    public function initialize(string $key, int $expiresIn): bool;

    /**
     * getCacheData - получить данные из кеша
     * @return scalar|array - может быть массив или одиночное значение любого типа
     */
    public function getData(): int|string|bool|float|array;

    /**
     * setCacheData - установить данные в кеш
     * @param mixed $data - может быть массив или одиночное значение
     * @return boolean true
     */
    public function setData(mixed $data): void;

    /**
     * clearThisCache - удалит текущий кеш (кеш связанный с текущим объектом)
     */
    public function clear(): void;
}
