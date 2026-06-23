<?php

declare(strict_types=1);

/**
 * @author Demyan Seleznev <seleznev@intervolga.ru>
 */

namespace Gy\Core;

use Gy\Core\AbstractClasses\Db;
use RuntimeException;

final class ServiceLocator
{
    private static ServiceLocator $instance;

    private ?Crypto $crypto = null;

    public static function getInstance(): self
    {
        return self::$instance ?? throw new RuntimeException('Service locator is uninitialized');
    }

    public function __construct(
        private readonly App $application,
        private readonly Db $databaseConnection,
        private readonly Configuration $configuration,
    ) {
        self::$instance = $this;
    }

    public function getApplication(): App
    {
        return $this->application;
    }

    public function getCryptoService(): Crypto
    {
        return $this->crypto ??= new Crypto();
    }

    public function getDatabaseConnection(): Db
    {
        return $this->databaseConnection;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }
}
