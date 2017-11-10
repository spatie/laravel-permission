<?php

namespace Spatie\Permission\Traits;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Tenant;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\RoleTenantUserPivot;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasTenants
{
    use HasRoles;

    /**
     * A user may have multiple roles.
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.tenant'),
            config('permission.table_names.role_tenant_user'),
            'user_id',
            'tenant_id'
        )->withPivot('role_id')
            ->join('roles', 'role_tenant_user.role_id', '=', 'roles.id')
            ->select(
                'roles.name as pivot_role_name'
            )
            ->using(config('permission.models.role_tenant_pivot'));
    }

    /**
     * Determine if the user may perform the given permission.
     *
     * @param string|\Spatie\Permission\Contracts\Permission $permission
     * @param string|\Spatie\Permission\Models\Tenant|\Spatie\Permission\Contracts\Tenant $tenant
     *
     * @return bool
     */
    public function hasPermissionToTenant($permission, $tenant): bool
    {
        if (is_string($permission)) {
            $permission = app(Permission::class)->findByName(
                $permission,
                $guardName ?? $this->getDefaultGuardName()
            );
        }

        return $this->hasDirectPermissionWithTenant($permission, $tenant) ||
            $this->hasPermissionViaRoleWithTenant($permission, $tenant);
    }

    /**
     * Determine if the user has the given permission.
     *
     * @param string|\Spatie\Permission\Contracts\Permission $permission
     * @param string|\Spatie\Permission\Contracts\Tenant $tenant
     *
     * @return bool
     */
    public function hasDirectPermissionWithTenant($permission, $tenant): bool
    {
        /*
         *  @toDo: Implement direct permission capabilities
         */

        return false;
    }

    /**
     * Determine if the user has, via roles, the given permission.
     *
     * @param \Spatie\Permission\Models\Permission $permission
     * @param string|\Spatie\Permission\Models\Tenant|\Spatie\Permission\Contracts\Tenant $tenant
     *
     * @return bool
     */
    protected function hasPermissionViaRoleWithTenant(Permission $permission, $tenant): bool
    {
        return $this->hasRoleWithTenant($permission->roles, $tenant);
    }

    /**
     * Determine if the user has (one of) the given role(s).
     *
     * @param string|\Illuminate\Database\Eloquent\Collection $role
     * @param string|\Spatie\Permission\Models\Tenant $tenant
     *
     * @return bool
     */
    public function hasRoleWithTenant($role, $tenant): bool
    {
        $columnTenantId = config('permission.foreign_keys.tenants.id');
        $tenantId = $tenant;
        if ($tenant instanceof Tenant) {
            $tenantId = $tenant->$columnTenantId;
        }

        if ($role instanceof Collection) {
            foreach ($role as $k => $v) {
                if ($this->tenants->where('pivot.tenant_id', $tenantId)->isNotEmpty()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Assign the given role to the user.
     *
     * @param array|int|\Spatie\Permission\Contracts\Role $roles
     * @param string|\Spatie\Permission\Contracts\Tenant $tenant
     *
     * @return HasTenants $this return this
     *
     * @throws RoleDoesNotExist when providing a role that is not found
     */
    public function assignRoleToTenant($roles, $tenant)
    {
        $tenantId = $this->getTenantId($tenant);

        if (is_string($roles)) {
            $roles = Role::findByName($roles)->id;
        }

        $rtuPivot = new RoleTenantUserPivot();
        if (is_array($roles)) {
            foreach ($roles as $k => $v) {
                $rtuPivot->attach($this->id, $v, $tenantId);
                $rtuPivot = new RoleTenantUserPivot();
            }
        } else {
            $rtuPivot->attach($this->id, $roles, $tenantId);
        }
        $this->forgetCachedPermissions();
        return $this;
    }

    /**
     * Revoke the given role and tenant from the user.
     *
     * @param array|int|\Spatie\Permission\Contracts\Role $roles
     * @param string|\Spatie\Permission\Contracts\Tenant $tenant
     *
     * @return $this
     *
     * @throws RoleDoesNotExist when providing a role that is not found
     */
    public function removeRoleFromTenant($roles, $tenant)
    {
        $tenantId = $this->getTenantId($tenant);
        $matches = [];

        if (is_string($roles)) {
            $roles = Role::findByName($roles)->id;
        }

        if ($roles instanceof Collection) {
            foreach ($roles as $k => $v) {
                $matches = $this->tenants->where('pivot.role_id', $v->id)->where('pivot.tenant_id', $tenantId);
            }
        } elseif (is_array($roles)) {
            $matches = $this->tenants->whereIn('pivot.role_id', array_values($roles))
                ->where('pivot.tenant_id', $tenantId);
        } else {
            $matches = $this->tenants->where('pivot.role_id', $roles)->where('pivot.tenant_id', $tenantId);
        }

        foreach ($matches as $match) {
            $match->pivot->detach();
        }

        $this->forgetCachedPermissions();
        return $this;
    }

    /**
     * Helper function to get the id of the tenant.
     *
     * @param string|\Spatie\Permission\Contracts\Tenant $tenant
     *
     * @return int $tenantId Id of the tenant
     */
    public function getTenantId($tenant)
    {
        $columnTenantId = config('permission.foreign_keys.tenants.id');
        if ($tenant instanceof Tenant) {
            $tenantId = $tenant->$columnTenantId;
        } else {
            $tenantId = $tenant;
        }

        return $tenantId;
    }
}
