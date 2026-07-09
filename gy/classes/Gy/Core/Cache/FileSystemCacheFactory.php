<?php

declare(strict_types=1);



namespace Gy\Core\Cache;

use Gy\Core\Contract\Cache\CacheFactoryInterface;
use Gy\Core\Contract\Cache\CacheInterface;

final readonly class FileSystemCacheFactory implements CacheFactoryInterface
{
    public function __construct(
        private string $projectUrl
    ) {}

    public function create(): CacheInterface
    {
        return new FileSystemCache($this->projectUrl);
    }
}
