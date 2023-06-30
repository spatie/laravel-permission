<?php

namespace Spatie\Permission\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\PermissionRegistrar;

trait HasBlockedPermission
{
    use HasPermissions;

    public function blockedPermissions(): MorphToMany
    {
        return $this->morphToMany(
            config('permission.models.permission'),
            'model',
            'model_has_blocked_permissions',
            'model_id',
            app(PermissionRegistrar::class)->pivotPermission);
    }

    public function blockFromPermission(...$permissions): void
    {
        $permissionsArray = $this->collectPermissions($permissions);

        $this->blockedPermissions()->sync($permissionsArray);
    }

    public function hasBlockFromPermission($permission): bool
    {
        $permission = $this->filterPermission($permission);

        return $this->blockedPermissions->contains($permission->getKeyName(), $permission->getKey());
    }

    public function hasBlockFromAnyPermission(...$permissions): bool
    {
        $permissions = collect($permissions)->flatten();

        foreach ($permissions as $permission) {
            if ($this->checkPermissionBlocked($permission)) {
                return true;
            }
        }

        return false;
    }

    public function checkPermissionBlocked($permission): bool
    {
        try {
            return $this->hasBlockFromPermission($permission);
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }
}
