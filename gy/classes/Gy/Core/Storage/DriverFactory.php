<?php

declare(strict_types=1);

namespace Gy\Core\Storage;

use Gy\Core\Configuration;
use Gy\Core\Contract\Storage\DriverInterface;

class DriverFactory
{
    final public function create(Configuration $configuration): DriverInterface
    {
        if (
            isset($config->databaseConfig['db_type'])
            && isset($config->databaseConfig['db_host'])
            && isset($config->databaseConfig['db_user'])
            && isset($config->databaseConfig['db_pass'])
            && isset($config->databaseConfig['db_name'])
        ) {
            global $DB;

            $db = match ($config->databaseConfig['db_type']) {
                'MySql' => new MySql($config),
                'PgSql' => new PgSql($config->databaseConfig),
                'PhpFileSqlClientForGy' => new PhpFileSqlClientForGy($config->databaseConfig),
            };
        } else {
            throw new RuntimeException('Database connection must be present');
        }
    }

    protected function resolveCustomDriver(Configuration $configuration): DriverInterface
    {

    }
}
