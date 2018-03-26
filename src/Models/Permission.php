<?php

namespace Spatie\Permission\Models;

use Spatie\Permission\Test\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\RefreshesPermissionCache;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Exceptions\PermissionAlreadyExists;
use Spatie\Permission\Contracts\Permission as PermissionContract;

class Permission extends Model implements PermissionContract
{
    use HasRoles;
    use RefreshesPermissionCache;

    public $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('permission.table_names.permissions'));
    }

    public static function create(array $attributes = [])
    {
        // TODO: Remove this exception and fall back to Laravel's default?
        if (static::getPermissions()->where('name', $attributes['name'])->first()) {
            throw PermissionAlreadyExists::create($attributes['name']);
        }

        return static::query()->create($attributes);
    }

    /**
     * A permission can be applied to roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.role'),
            config('permission.table_names.role_has_permissions')
        );
    }

    /**
     * A permission belongs to some users/permission things.
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(
            User::class, // TEMP! - getModelForGuard($this->attributes['guard_name']),
            'model',
            config('permission.table_names.model_has_permissions'),
            'permission_id',
            'model_id'
        );
    }

    /**
     * Find a permission by its name.
     *
     * @param string $name
     *
     * @throws \Spatie\Permission\Exceptions\PermissionDoesNotExist
     *
     * @return \Spatie\Permission\Contracts\Permission
     */
    public static function findByName(string $name): PermissionContract
    {
        $permission = static::getPermissions()->where('name', $name)->first();

        if (! $permission) {
            throw PermissionDoesNotExist::create($name);
        }

        return $permission;
    }

    /**
     * Find a permission by its id.
     *
     * @param int $id
     *
     * @throws \Spatie\Permission\Exceptions\PermissionDoesNotExist
     *
     * @return \Spatie\Permission\Contracts\Permission
     */
    public static function findById(int $id): PermissionContract
    {
        $permission = static::getPermissions()->where('id', $id)->first();

        if (! $permission) {
            throw PermissionDoesNotExist::withId($id);
        }

        return $permission;
    }

    /**
     * Find or create permission by its name).
     *
     * @param string $name
     *
     * @return \Spatie\Permission\Contracts\Permission
     */
    public static function findOrCreate(string $name): PermissionContract
    {
        $permission = static::getPermissions()->where('name', $name)->first();

        if (! $permission) {
            return static::create(['name' => $name]);
        }

        return $permission;
    }

    /**
     * Get the current cached permissions.
     */
    protected static function getPermissions(): Collection
    {
        return app(PermissionRegistrar::class)->getPermissions();
    }
}
