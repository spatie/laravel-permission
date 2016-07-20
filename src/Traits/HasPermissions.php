<?php

namespace Spatie\Permission\Traits;

use Spatie\Permission\Contracts\Permission;

trait HasPermissions
{
    /**
     * Grant the given permission(s) to a role.
     *
     * @param string|array|Permission|\Illuminate\Support\Collection $permissions
     * @param array $params
     *
     * @return HasPermissions
     */
    public function givePermissionTo($permissions, ...$params)
    {
        $joinings = $this->resolveJoiningAttributes($params);

        if (is_string($permissions) && $this->usingMultipleArguments($params)) {
            array_unshift($params, $permissions);
            $permissions = $params;
        }

        $this->savePermissions($permissions, $joinings);

        $this->forgetCachedPermissions();

        return $this;
    }
    
    /**
     * Save the given permissions to a role.
     *
     * @param array|Permission|\Illuminate\Support\Collection $permissions
     * @param array $joinings
     * 
     * @return array|\Illuminate\Database\Eloquent\Model
     */
    protected function savePermissions($permissions, array $joinings = [])
    {
        $permissions = $this->getStoredPermission($permissions);

        if($permissions instanceof \Illuminate\Database\Eloquent\Collection) {
            return $this->permissions()->saveMany($permissions, $joinings);
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
        return count($params) > 0;
    }

    /**
     * @param $params
     * @return array
     */
    protected function resolveJoiningAttributes(&$params)
    {
        if(is_array(end($params)) && !empty($params)) {
            return array_pop($params);
        }

        return [];
    }
}
