<?php

namespace Gy\Core\Cache;

use Gy\Core\AbstractClasses\Cache;
use RuntimeException;

if (!defined("GY_CORE") && (GY_CORE !== true)) {
    die("gy: err include core");
}

/**
 * cache - класс для работы с кешем
 * для даботы нужен раздел gy/cache/
 */
class CacheFiles extends Cache
{
    private string $cacheUrl = '/cache/';
    private array $data = [];
    private string $key = 'noneme';
    private int $expiresIn = 86_400; // In seconds.
    private string $endUrl = '.php';

    /**
     *
     * @param type $projectUrl - путь к проекту
     */
    public function __construct(
        private readonly string $projectUrl = '/',
    ) {}

    private function createCacheDirectory(): void
    {
        $cacheDirectory = $this->projectUrl . $this->cacheUrl;

        if (\is_dir($cacheDirectory)) {
            return;
        }

        try {
            \set_error_handler(static fn() => true);
            $result = \mkdir($cacheDirectory, 0755, true);
        } finally {
            \restore_error_handler();
        }

        if (!$result) {
            throw new RuntimeException('Failed to create cache directory');
        }
    }

    /**
     * cacheInit - инициализация кеша, надо проверить есть кеш по заданным параметрам
     * @param string $key
     * @param int $expiresIn - время кеширования в секундах
     * @return boolean
     */
    public function cacheInit(string $key, int $expiresIn): bool
    {
        $this->createCacheDirectory();

        $this->key = $key;
        $this->expiresIn = $expiresIn;

        if (!\is_file($this->projectUrl . $this->cacheUrl . $this->key . $this->endUrl)) {
            return false;
        }

        $cacheData = include $this->projectUrl . $this->cacheUrl . $this->key . $this->endUrl;

        if (!empty($cacheData)) {
            $cacheData = json_decode($cacheData, true);

            $expiresAt = (int)$cacheData['createTime'] + (int)$cacheData['cacheTime'];
            if ($expiresAt <= \time()) {
                return false;
            }

            $this->data = $cacheData['data'];
        }

        return $this->data !== [];
    }

    /**
     * getCacheData - получить данные из кеша
     * @return scalar|array - может быть массив или одиночное значение любого типа
     */
    public function getData(): int|string|bool|float|array
    {
        return $this->data;
    }

    /**
     * setCacheData - установить данные в кеш
     * @param mixed $data - может быть массив или одиночное значение
     * @return boolean true
     */
    public function setData(mixed $data): void
    {
        $cacheData = [
            'data' => $data,
            'createTime' => time(),
            'cacheTime' => $this->expiresIn,
        ];

        \file_put_contents(
            $this->projectUrl . $this->cacheUrl . $this->key . $this->endUrl,
            '<?php return ' . "'" . \json_encode($cacheData) . "';",
        );
    }

    /**
     * clearThisCache - удалит текущий кеш (кеш связанный с текущим объектом)
     */
    public function clear(): void
    {
        $cacheFile = $this->projectUrl . $this->cacheUrl . $this->key . $this->endUrl;

        if (\is_file($cacheFile)) {
            \unlink($cacheFile);
        }
    }
}
