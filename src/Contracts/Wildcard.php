<?php

namespace Spatie\Permission\Contracts;

use MongoDB\Laravel\Eloquent\Model;

interface Wildcard
{
    public function __construct(Model $record);

    public function getIndex(): array;

    public function implies(string $permission, string $guardName, array $index): bool;
}
