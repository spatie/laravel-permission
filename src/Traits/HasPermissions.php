<?php

namespace Spatie\Permission\Traits;

use Spatie\Permission\Contracts\Permission;

trait HasPermissions
{
    /**
     * Grant the given permission to a role.
     *
     * @param  $permission
     *
     * @return HasPermissions
     */
    public function givePermissionTo($permission)
    {
        $this->permissions()->save($this->getStoredPermission($permission));

        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * Revoke the given permission.
     *
     * @param $permission
     *
     * @return HasPermissions
     */
    public function revokePermissionTo($permission)
    {
        $this->permissions()->detach($this->getStoredPermission($permission));

        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * @param $permission
     *
     * @return Permission
     */
    protected function getStoredPermission($permission)
    {
        if (is_string($permission)) {
            return app(Permission::class)->findByName($permission);
        }

        return $permission;
    }
}
