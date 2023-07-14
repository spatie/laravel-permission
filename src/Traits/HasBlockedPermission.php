<?php

namespace Spatie\Permission\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\PermissionRegistrar;

trait HasBlockedPermission
{
    abstract function collectPermissions(...$permissions);
    abstract function filterPermission($permission, $guardName = null);

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

        $this->blockedPermissions()->attach($permissionsArray);
    }

    public function unblockFromPermission($permission): void
    {
        $this->blockedPermissions()->detach($this->getStoredPermission($permission));

        $this->unsetRelation('blockedPermissions');
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

    private function checkPermissionBlocked($permission): bool
    {
        try {
            return $this->hasBlockFromPermission($permission);
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }
}
