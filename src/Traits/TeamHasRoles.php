<?php

namespace Spatie\Permission\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\PermissionRegistrar;

trait TeamHasRoles
{
    public static function bootTeamHasRoles()
    {
        static::deleting(function ($model) {
            $modelHasSoftDeleting = method_exists($model, 'isForceDeleting');
            $roleHasSoftDeleting = method_exists(app(PermissionRegistrar::class)->getRoleClass(), 'isForceDeleting');

            if ($modelHasSoftDeleting && ! $model->isForceDeleting()) {
                if ($roleHasSoftDeleting) {
                    $model->specific_roles()->delete();
                }

                return;
            }

            $model->roles()->detach();
            $model->permissions()->detach();

            $roleDelete = $roleHasSoftDeleting && $modelHasSoftDeleting && $model->isForceDeleting() ? 'forceDelete' : 'delete';
            $model->specific_roles()->$roleDelete();
        });
    }

    /**
     * A team may have multiple roles on multiple models.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            app(PermissionRegistrar::class)->getRoleClass(),
            config('permission.table_names.model_has_roles'),
            PermissionRegistrar::$teamsKey,
            PermissionRegistrar::$pivotRole
        );
    }

    /**
     * A team may have multiple specific roles.
     */
    public function specific_roles(): HasMany
    {
        return $this->hasMany(
            app(PermissionRegistrar::class)->getRoleClass(),
            PermissionRegistrar::$teamsKey
        );
    }

    /**
     * A team may have multiple direct permissions on multiple models.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            app(PermissionRegistrar::class)->getPermissionClass(),
            config('permission.table_names.model_has_permissions'),
            PermissionRegistrar::$teamsKey,
            PermissionRegistrar::$pivotPermission
        );
    }
}
