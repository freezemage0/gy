<?php

declare(strict_types=1);

/**
 * @author Demyan Seleznev <seleznev@intervolga.ru>
 */

namespace Gy\Core;

use Gy\Core\Storage\Driver;
use RuntimeException;

final class Container
{
    private static Container $instance;

    private ?Crypto $crypto = null;

    public static function getInstance(): self
    {
        return self::$instance ?? throw new RuntimeException('Service locator is uninitialized');
    }

    public function __construct(
        private readonly Application $application,
        private readonly Driver $databaseConnection,
        private readonly Configuration $configuration,
    ) {
        self::$instance = $this;
    }

    public function getApplication(): Application
    {
        return $this->application;
    }

    public function getCryptoService(): Crypto
    {
        return $this->crypto ??= new Crypto();
    }

    public function getDatabaseConnection(): Driver
    {
        return $this->databaseConnection;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getModuleManager(): ModuleManager
    {
        return $this->moduleManager ??= new ModuleManager();
    }
}
