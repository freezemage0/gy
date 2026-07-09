<?php

declare(strict_types=1);

namespace Gy\Core\Contract\Storage;

interface ResultInterface
{
    public function fetch(): ?array;

    public function fetchAll(): array;
}
