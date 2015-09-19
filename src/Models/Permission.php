<?php

namespace Spatie\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Traits\RefreshesPermissionCache;

class Permission extends Model
{
    use RefreshesPermissionCache;

    public $guarded = ['id'];

    public function __construct()
    {
        parent::__construct();
        $this->setTable(config('laravel-permissions.tables.permissions'));
    }

    /**
     * A permission can be applied to roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, config('laravel-permissions.tables.role_has_permissions'));
    }

    /**
     * Find a permission by its name.
     *
     * @param string $name
     *
     * @throws PermissionDoesNotExist
     */
    public static function findByName($name)
    {
        $permission = static::where('name', $name)->first();

        if (!$permission) throw new PermissionDoesNotExist();

        return $permission;
    }
}
