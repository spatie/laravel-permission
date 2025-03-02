<?php

namespace Spatie\Permission;

use Spatie\Permission\Contracts\PermissionsTeamResolver;

class DefaultTeamResolver implements PermissionsTeamResolver
{
    protected int|string|null $teamId = null;

    /**
     * Set the team id for teams/groups support, this id is used when querying permissions/roles
     *
     * @param  int|string|\Illuminate\Database\Eloquent\Model|null  $id
     */
    public function setPermissionsTeamId($id): void
    {
        if ($id instanceof \Illuminate\Database\Eloquent\Model) {
            $id = $id->getKey();
        }
        $this->teamId = $id;
    }

    public function getPermissionsTeamId(): int|string|null
    {
        return $this->teamId;
    }
}
