<?php

namespace Spatie\Permission\Traits;

use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Exceptions\PermissionMustNotBeEmpty;

trait HasPermissions
{
    /**
     * Grant the given permission(s) to a role.
     *
     * @param string|array|Permission|\Illuminate\Support\Collection $permissions
     *
     * @return HasPermissions
     */
    public function givePermissionTo(...$permissions)
    {
        if(count($permissions) < 1) {
            throw new PermissionMustNotBeEmpty();
        }

        if (!$this->usingMultipleArguments($permissions)) {
            $permissions = current($permissions);
        }

        $this->savePermissions($permissions);

        $this->forgetCachedPermissions();

        return $this;
    }
    
    /**
     * Save the given permissions to a role.
     *
     * @param array|Permission|\Illuminate\Support\Collection $permissions
     * 
     * @return array|\Illuminate\Database\Eloquent\Model
     */
    protected function savePermissions($permissions)
    {
        $permissions = $this->getStoredPermission($permissions);

        if($permissions instanceof \Illuminate\Database\Eloquent\Collection) {
            return $this->permissions()->saveMany($permissions);
        }

        return $this->permissions()->save($permissions);
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
     * @param string|array|Permission|\Illuminate\Support\Collection $permissions
     *
     * @return Permission
     */
    protected function getStoredPermission($permissions)
    {
        if (is_string($permissions)) {
            return app(Permission::class)->findByName($permissions);
        }

        if (is_array($permissions)) {
            return app(Permission::class)->whereIn('name', $permissions)->get();
        }

        return $permissions;
    }

    /**
     * @param $params
     * @return bool
     */
    private function usingMultipleArguments($params)
    {
        return count($params) > 1;
    }

}
