<?php

namespace Spatie\Permission\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RoleTenantUserPivot extends Pivot
{
    protected $table = 'role_tenant_user';
    protected $fillable = ['role_id', 'tenant_id', 'user_id'];

    /**
     * Setup the relationship for the roles.
     *
     * @return BelongsToMany the return of the relationship
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.role')
        );
    }

    /**
     * Setup the relationship for the tenants.
     *
     * @return BelongsToMany the return of the relationship
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.tenant')
        );
    }

    /**
     * Attaches the relationship of the tenant, user and role.
     *
     * @return $this
     */
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

    /**
     * Removes the relationship of the tenant, user and role.
     *
     * @param int $userId
     * @param int $roleId
     * @param int|string $tenantId
     *
     * @return $this
     */
    public function attach($userId, $roleId, $tenantId)
    {
        $this->user_id = $userId;
        $this->role_id = $roleId;
        $this->tenant_id = $tenantId;
        $this->save();
    }
}
