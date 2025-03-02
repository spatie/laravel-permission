<?php

namespace Spatie\Permission\Contracts;

use Illuminate\Database\Eloquent\Model;

interface Wildcard
{
    public function __construct(Model $record);

    public function getIndex(): array;

    public function implies(string $permission, string $guardName, array $index): bool;
}
