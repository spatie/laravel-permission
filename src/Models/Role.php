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
        if (is_string($permission)) {
            $permission = Permission::whereName($permission)->firstOrFail();
        }

        $this->permissions()->save($permission);

        $this->forgetCachedPermissions();

        return $this;
    }
}
