<?php

declare(strict_types=1);



namespace Gy\Core;

use RuntimeException;
use SensitiveParameter;

final class Configuration
{
    public function __construct(
        public string $language,
        public string $salt,
        public array $databaseConfig,
        public string $page404,
        public string $cacheType,
        public string $version,
        public string $publicDirectory,
        public string $projectRoot,
        #[SensitiveParameter] public ?string $appSecret
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
            appSecret: $config['secretKeyAuthorizationAdminPanel'] ?? null
        );
    }
}
