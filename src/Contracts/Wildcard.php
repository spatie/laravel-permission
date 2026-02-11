<?php

namespace Spatie\Permission\Contracts;

interface Wildcard
{
    public function getIndex(): array;

    public function implies(string $permission, string $guardName, array $index): bool;
}
