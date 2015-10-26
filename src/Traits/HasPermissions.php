<?php

namespace Spatie\Permission\Traits;

use Spatie\Permission\Contracts\PermissionContract;
use Spatie\Permission\Contracts\HasPermissionsContract;

trait HasPermissions
{
    /**
     * Grant the given permission to a role.
     *
     * @param  $permission
     *
     * @return HasPermissionsContract
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
     * @return HasPermissionsContract
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
     * @return PermissionContract
     */
    protected function getStoredPermission($permission)
    {
        if (is_string($permission)) {
            return app(PermissionContract::class)->findByName($permission);
        }

        return $permission;
    }
}
