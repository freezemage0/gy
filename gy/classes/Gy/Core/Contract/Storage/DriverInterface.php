<?php

declare(strict_types=1);



namespace Gy\Core\Contract\Storage;

interface DriverInterface
{
    public function query(string $query);
}
