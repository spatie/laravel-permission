<?php

namespace Spatie\Permission\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RoleTenantUserPivot extends Pivot {

    protected $table = 'role_tenant_user';
    protected $fillable = ['role_id', 'tenant_id', 'user_id'];

    /**
     * Determine if the user may perform the given permission.
     *
     * @param string|Permission $permission
     * @param string| $permission
     *
     * @return bool
     *
     * @throws \Spatie\Permission\Exceptions\GuardDoesNotMatch
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.role')
        );
    }

    /**
     * Determine if the user may perform the given permission.
     *
     * @param string|Tenants $tenants
     * @param string| $tenants
     *
     * @return bool
     *
     * @throws \Spatie\Permission\Exceptions\GuardDoesNotMatch
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.tenant')
        );
    }

    public function detach()
    {
        $userId = $this->user_id;
        $roleId = $this->role_id;
        $tenantId = $this->tenant_id;

        $this->when($userId, function ($query) use ($userId) {
            return $query->where('user_id', $userId);
        })->when($roleId, function ($query) use ($roleId) {
            return $query->where('role_id', $roleId);
        })->when($tenantId, function ($query) use ($tenantId) {
            return $query->where('tenant_id', $tenantId);
        })->delete();

        return $this;
    }

    public function attach($userId, $roleId, $tenantId)
    {
        $this->user_id = $userId;
        $this->role_id = $roleId;
        $this->tenant_id = $tenantId;
        $this->save();
    }

}