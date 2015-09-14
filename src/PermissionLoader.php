<?php

namespace Spatie\Permission;

use Exception;
use Illuminate\Contracts\Auth\Access\Gate;
use Spatie\Permission\Models\Permission;

class PermissionLoader
{
    /**
     * @var Gate
     */
    protected $gate;

    public function __construct(Gate $gate)
    {
        $this->gate = $gate;
    }

    public function registerPermissions()
    {
        try {
            foreach ($this->getPermissions() as $permission) {
                $this->gate->define($permission->name, function ($user) use ($permission) {
                    return $user->hasRole($permission->roles);
                });
            }
        } catch (Exception $e) {
            \Log::alert('Could not register permissions');
        }
    }

    public function getPermissions()
    {
        return Permission::with('roles')->get();
    }
}
