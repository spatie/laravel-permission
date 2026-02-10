<?php

namespace Spatie\Permission\Contracts;

use Illuminate\Database\Eloquent\Model;

interface PermissionsTeamResolver
{
    public function getPermissionsTeamId(): int|string|null;

    public function setPermissionsTeamId(int|string|Model|null $id): void;
}
