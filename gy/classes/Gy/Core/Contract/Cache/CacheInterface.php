<?php

declare(strict_types=1);



namespace Gy\Core\Contract\Cache;


/**
 * Предоставляет API для управления кэшированием.
 */
interface CacheInterface
{
    /**
     * @param string $key
     * @param int $expiresIn - время кеширования в секундах
     *
     * @return bool
     */
    public function initialize(string $key, int $expiresIn): bool;

    /**
     * Получает данные из кэша.
     *
     * @return scalar|array - может быть массив или одиночное значение любого типа
     */
    public function getData(): int|string|bool|float|array;

    /**
     * Записывает данные в кэш.
     *
     * @param scalar|array $data Скалярное значение.
     */
    public function setData(int|string|bool|float|array $data): void;

    /**
     * Сбрасывает кэш.
     */
    public function clear(): void;
}
