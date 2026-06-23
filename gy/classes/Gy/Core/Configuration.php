<?php

declare(strict_types=1);

/**
 * @author Demyan Seleznev <seleznev@intervolga.ru>
 */

namespace Gy\Core;

use RuntimeException;

final class Configuration
{
    public function __construct(
        public readonly string $language,
        public readonly string $salt,
        public readonly array $databaseConfig,
        public readonly string $page404,
        public readonly string $cacheType,
        public string $version,
        public readonly string $publicDirectory,
        public readonly string $projectRoot,
    ) {}

    public static function createFromFile(string $file): Configuration
    {
        if (!\is_file($file)) {
            throw new RuntimeException('Config file not found');
        }

        $config = include $file;

        return new Configuration(
            language: $config['lang'],
            salt: $config['sole'],
            databaseConfig: $config['db_config'],
            page404: $config['urlPage404'],
            cacheType: $config['type_cache'],
            version: $config['v-gy'],
            publicDirectory: $config['dir_public_file'],
            projectRoot: \dirname($file),
        );
    }
}
