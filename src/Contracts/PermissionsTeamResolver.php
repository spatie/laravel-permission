<?php

namespace Spatie\Permission\Contracts;

interface PermissionsTeamResolver
{
    public function getPermissionsTeamId(): int|string|null;

    /**
     * Set the team id for teams/groups support, this id is used when querying permissions/roles
     *
     * @param  int|string|\Illuminate\Database\Eloquent\Model|null  $id
     */
    public function setPermissionsTeamId($id): void;
}
