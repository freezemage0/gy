<?php

declare(strict_types=1);

/**
 * @author Demyan Seleznev <seleznev@intervolga.ru>
 */

namespace Gy\Core\Contract\Cache;

interface CacheFactoryInterface
{
    public function create(): CacheInterface;
}
