<?php

namespace Spatie\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\RefreshesPermissionCache;

class Role extends Model
{
    use RefreshesPermissionCache;

    public $guarded = ['id'];

    /**
     * A role may be given various permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Grant the given permission to a role.
     *
     * @param  $permission
     *
     * @return this
     */
    public function givePermissionTo($permission)
    {
        $this->permissions()->save($this->getStoredPermission($permission));

        $this->forgetCachedPermissions();

        return $this;
    }

    public function revokePermissionTo($permission)
    {
        $this->permissions()->detach($this->getStoredPermission($permission));

        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * @param $permission
     *
     * @return mixed
     */
    protected function getStoredPermission($permission)
    {
        if (is_string($permission)) {
            return Permission::whereName($permission)->firstOrFail();
        }

        return $permission;
    }
}
