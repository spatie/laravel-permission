<?php
/**
 * Copyright (c) Padosoft.com 2018.
 */

namespace Spatie\Permission\Test;

use Illuminate\Support\Facades\Event;
use Spatie\Permission\Events\PermissionRevoked;
use Spatie\Permission\Events\PermissionSynched;
use Spatie\Permission\Events\RoleAssigned;
use Spatie\Permission\Events\RoleRevoked;
use Spatie\Permission\Events\RoleSynched;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Events\PermissionAssigned;

class EventTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        Permission::create(['name' => 'other-permission']);
    }

    /** @test */
    public function it_fires_an_event_on_permission_assigned_to_role()
    {
        Event::fake();

        $this->testUserRole->givePermissionTo('other-permission');
        $role = $this->testUserRole;
        Event::assertDispatched(PermissionAssigned::class, function ($event) use ($role) {
            return count($event->permissions) === 1 && $event->target == $role;
        });
    }

    /** @test */
    public function it_fires_an_event_on_role_assigned_to_permission()
    {
        Event::fake();
        $role = $this->testUserRole;
        $perm = Permission::create(['name' => 'other-permission2']);
        $perm->assignRole($role);

        Event::assertDispatched(PermissionAssigned::class, function ($event) use ($role) {
            return count($event->permissions) === 1 && $event->target == $role;
        });
    }

    /** @test */
    public function it_fires_an_event_on_role_synched_to_permission()
    {
        Event::fake();
        $role = $this->testUserRole;
        $role2 = app(Role::class)->findByName('testRole2');
        $perm = Permission::create(['name' => 'other-permission2']);
        $perm->disablePermissionEvents();
        $perm = $perm->syncRoles('testRole');
        Event::assertNotDispatched(PermissionAssigned::class);
        $perm->enablePermissionEvents();
        $perm = $perm->syncRoles('testRole2');
        Event::assertDispatched(PermissionAssigned::class, function ($event) use ($role2) {
            return count($event->permissions) === 1 && $event->target->id == $role2->id;
        });
        Event::assertDispatched(PermissionRevoked::class, function ($event) use ($role) {
            return count($event->permissions) === 1 && $event->target->id == $role->id;
        });
    }

    /** @test */
    public function it_fires_an_event_on_permission_synched_to_role()
    {
        Event::fake();

        $this->testUserRole->syncPermissions('other-permission', 'edit-articles');
        $role = $this->testUserRole;
        Event::assertDispatched(PermissionSynched::class, function ($event) use ($role) {
            return count($event->permissions_revoked) === 0 && count($event->permissions_added) === 2 && count($event->permissions_assigned) === 2 && $event->target == $role;
        });
    }

    /** @test */
    public function it_fires_an_event_on_permission_assigned_to_user()
    {
        Event::fake();

        $this->testUser->givePermissionTo('other-permission');
        $user = $this->testUser;
        Event::assertDispatched(PermissionAssigned::class, function ($event) use ($user) {
            return count($event->permissions) === 1 && $event->target == $user;
        });
    }

    /** @test */
    public function it_fires_an_event_on_permission_synched_to_user()
    {
        Event::fake();

        $this->testUser->syncPermissions('other-permission', 'edit-articles');
        $user = $this->testUser;
        Event::assertDispatched(PermissionSynched::class, function ($event) use ($user) {
            return count($event->permissions_revoked) === 0 && count($event->permissions_added) === 2 && count($event->permissions_assigned) === 2 && $event->target == $user;
        });
    }

    /** @test */
    public function it_fires_an_event_on_permission_revoked_to_role()
    {
        Event::fake();

        $this->testUserRole->revokePermissionTo('edit-articles');
        $role = $this->testUserRole;
        Event::assertDispatched(PermissionRevoked::class, function ($event) use ($role) {
            return count($event->permissions) === 1 && $event->target == $role;
        });
    }

    /** @test */
    public function it_fires_an_event_on_role_revoked_from_role()
    {
        Event::fake();
        $role = $this->testUserRole;
        $this->testUserPermission->removeRole($role);

        Event::assertDispatched(PermissionRevoked::class, function ($event) use ($role) {
            return count($event->permissions) === 1 && $event->target == $role;
        });
    }

    /** @test */
    public function it_fires_an_event_on_permission_revoked_to_user()
    {
        Event::fake();

        $this->testUser->revokePermissionTo('edit-articles');
        $user = $this->testUser;
        Event::assertDispatched(PermissionRevoked::class, function ($event) use ($user) {
            return count($event->permissions) === 1 && $event->target == $user;
        });
    }

    /** @test */
    public function it_fires_an_event_on_role_assigned_to_user()
    {
        Event::fake();

        $this->testUser->assignRole('testRole');
        $user = $this->testUser;
        Event::assertDispatched(RoleAssigned::class, function ($event) use ($user) {
            return count($event->roles) === 1 && $event->target == $user;
        });
    }

    /** @test */
    public function it_fires_an_event_on_roles_synched_to_user()
    {
        Event::fake();

        $this->testUser->syncRoles('testRole', 'testRole2');
        $user = $this->testUser;
        Event::assertNotDispatched(RoleAssigned::class);
        Event::assertDispatched(RoleSynched::class, function ($event) use ($user) {
            return count($event->roles_revoked) === 0 && count($event->roles_added) === 2 && count($event->roles_assigned) === 2 && $event->target == $user;
        });
    }

    /** @test */
    public function it_fires_an_event_on_role_revoked_to_user()
    {
        Event::fake();

        $this->testUser->removeRole('testRole');
        $user = $this->testUser;
        Event::assertDispatched(RoleRevoked::class, function ($event) use ($user) {
            return count($event->roles) === 1 && $event->target == $user;
        });
    }
}
