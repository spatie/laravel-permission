<?php

namespace Spatie\Permission\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
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
     * @return mixed
     */
    public function givePermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::whereName($permission)->firstOrFail();
        }

        return $this->permissions()->save($permission);
    }
}
