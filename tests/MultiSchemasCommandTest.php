<?php

namespace Spatie\Permission\Tests;

use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\PermissionRegistrar;

class MultiSchemasCommandTest extends MultiSchemasTestCase
{
    /** @test */
    public function it_can_create_a_role()
    {
        $permissionRegistrarAbstract = 'PermissionRegistrarApp2';
        /** @var PermissionRegistrar $permissionRegistrar */
        $permissionRegistrar = $this->app->make($permissionRegistrarAbstract);

        Artisan::call('permission:create-role', [
            'name' => 'new-role',
            '--permission-registrar' => $permissionRegistrarAbstract,
        ]);

        /** @var Role $roleClass */
        $roleClass = $permissionRegistrar->getRoleClass();

        $this->assertCount(1, $roleClass::where('name', 'new-role')->get());
        $this->assertCount(0, $roleClass::where('name', 'new-role')->first()->permissions);
    }

    /** @test */
    public function it_can_create_a_role_with_a_specific_guard()
    {
        $permissionRegistrarAbstract = 'PermissionRegistrarApp2';
        /** @var PermissionRegistrar $permissionRegistrar */
        $permissionRegistrar = $this->app->make($permissionRegistrarAbstract);

        Artisan::call('permission:create-role', [
            'name' => 'new-role',
            'guard' => 'api',
            '--permission-registrar' => $permissionRegistrarAbstract,
        ]);

        /** @var Role $roleClass */
        $roleClass = $permissionRegistrar->getRoleClass();

        $this->assertCount(1, $roleClass::where('name', 'new-role')
            ->where('guard_name', 'api')
            ->get());
    }

    /** @test */
    public function it_can_create_a_permission()
    {
        $permissionRegistrarAbstract = 'PermissionRegistrarApp2';
        /** @var PermissionRegistrar $permissionRegistrar */
        $permissionRegistrar = $this->app->make($permissionRegistrarAbstract);

        Artisan::call('permission:create-permission', [
            'name' => 'new-permission',
            '--permission-registrar' => $permissionRegistrarAbstract,
        ]);

        /** @var Permission $permissionClass */
        $permissionClass = $permissionRegistrar->getPermissionClass();

        $this->assertCount(1, $permissionClass::where('name', 'new-permission')->get());
    }

    /** @test */
    public function it_can_create_a_permission_with_a_specific_guard()
    {
        $permissionRegistrarAbstract = 'PermissionRegistrarApp2';
        /** @var PermissionRegistrar $permissionRegistrar */
        $permissionRegistrar = $this->app->make($permissionRegistrarAbstract);

        Artisan::call('permission:create-permission', [
            'name' => 'new-permission',
            'guard' => 'api',
            '--permission-registrar' => $permissionRegistrarAbstract,
        ]);

        /** @var Permission $permissionClass */
        $permissionClass = $permissionRegistrar->getPermissionClass();

        $this->assertCount(1, $permissionClass::where('name', 'new-permission')
            ->where('guard_name', 'api')
            ->get());
    }

    /** @test */
    public function it_can_create_a_role_and_permissions_at_same_time()
    {
        $permissionRegistrarAbstract = 'PermissionRegistrarApp2';
        /** @var PermissionRegistrar $permissionRegistrar */
        $permissionRegistrar = $this->app->make($permissionRegistrarAbstract);

        Artisan::call('permission:create-role', [
            'name' => 'new-role',
            'permissions' => 'first permission | second permission',
            '--permission-registrar' => $permissionRegistrarAbstract,
        ]);

        /** @var Role $roleClass */
        $roleClass = $permissionRegistrar->getRoleClass();

        $role = $roleClass::where('name', 'new-role')->first();

        $this->assertTrue($role->hasPermissionTo('first permission'));
        $this->assertTrue($role->hasPermissionTo('second permission'));
    }

    /** @test */
    public function it_can_create_a_role_without_duplication()
    {
        $permissionRegistrarAbstract = 'PermissionRegistrarApp2';
        /** @var PermissionRegistrar $permissionRegistrar */
        $permissionRegistrar = $this->app->make($permissionRegistrarAbstract);

        Artisan::call('permission:create-role', [
            'name' => 'new-role',
            '--permission-registrar' => $permissionRegistrarAbstract,
        ]);
        Artisan::call('permission:create-role', [
            'name' => 'new-role',
            '--permission-registrar' => $permissionRegistrarAbstract,
        ]);

        /** @var Role $roleClass */
        $roleClass = $permissionRegistrar->getRoleClass();

        $this->assertCount(1, $roleClass::where('name', 'new-role')->get());
        $this->assertCount(0, $roleClass::where('name', 'new-role')->first()->permissions);
    }

    /** @test */
    public function it_can_create_a_permission_without_duplication()
    {
        $permissionRegistrarAbstract = 'PermissionRegistrarApp2';
        /** @var PermissionRegistrar $permissionRegistrar */
        $permissionRegistrar = $this->app->make($permissionRegistrarAbstract);

        Artisan::call('permission:create-permission', [
            'name' => 'new-permission',
            '--permission-registrar' => $permissionRegistrarAbstract,
        ]);
        Artisan::call('permission:create-permission', [
            'name' => 'new-permission',
            '--permission-registrar' => $permissionRegistrarAbstract,
        ]);

        /** @var Permission $permissionClass */
        $permissionClass = $permissionRegistrar->getPermissionClass();

        $this->assertCount(1, $permissionClass::where('name', 'new-permission')->get());
    }

    /** @test */
    public function it_can_show_permission_tables()
    {
        $permissionRegistrarAbstract = 'PermissionRegistrarApp2';
        /** @var PermissionRegistrar $permissionRegistrar */
        $permissionRegistrar = $this->app->make($permissionRegistrarAbstract);

        /** @var Role $roleClass */
        $roleClass = $permissionRegistrar->getRoleClass();
        /** @var Permission $permissionClass */
        $permissionClass = $permissionRegistrar->getPermissionClass();

        $roleClass::create(['name' => 'testRole']);
        $roleClass::create(['name' => 'testRole_2']);
        $permissionClass::create(['name' => 'edit-articles']);

        Artisan::call('permission:show', [
            '--permission-registrar' => $permissionRegistrarAbstract,
        ]);

        $output = Artisan::output();

        $this->assertTrue(strpos($output, 'Guard: web') !== false);

        // |               | testRole | testRole_2 |
        // | edit-articles |  ·       |  ·         |
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('/\|\s+\|\s+testRole\s+\|\s+testRole_2\s+\|/', $output);
            $this->assertMatchesRegularExpression('/\|\s+edit-articles\s+\|\s+·\s+\|\s+·\s+\|/', $output);
        } else { // phpUnit 9/8
            $this->assertRegExp('/\|\s+\|\s+testRole\s+\|\s+testRole_2\s+\|/', $output);
            $this->assertRegExp('/\|\s+edit-articles\s+\|\s+·\s+\|\s+·\s+\|/', $output);
        }

        $roleClass::findByName('testRole')->givePermissionTo('edit-articles');
        $permissionRegistrar->forgetCachedPermissions();

        Artisan::call('permission:show', [
            '--permission-registrar' => $permissionRegistrarAbstract,
        ]);

        $output = Artisan::output();

        // | edit-articles |  ·       |  ·        |
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('/\|\s+edit-articles\s+\|\s+✔\s+\|\s+·\s+\|/', $output);
        } else {
            $this->assertRegExp('/\|\s+edit-articles\s+\|\s+✔\s+\|\s+·\s+\|/', $output);
        }
    }

    /** @test */
    public function it_can_show_permissions_for_guard()
    {
        $permissionRegistrarAbstract = 'PermissionRegistrarApp2';

        Artisan::call('permission:show', [
            'guard' => 'web',
            '--permission-registrar' => $permissionRegistrarAbstract,
        ]);

        $output = Artisan::output();

        $this->assertTrue(strpos($output, 'Guard: web') !== false);
        $this->assertTrue(strpos($output, 'Guard: admin') === false);
    }

    /** @test */
    public function it_can_show_roles_by_teams()
    {
        config()->set('permission.teams', true);

        $permissionRegistrarAbstract = 'PermissionRegistrarApp2';
        /** @var PermissionRegistrar $permissionRegistrar */
        $permissionRegistrar = $this->app->make($permissionRegistrarAbstract);

        /** @var Role $roleClass */
        $roleClass = $permissionRegistrar->getRoleClass();

        $roleClass::where('name', 'testRole2')->delete();
        $roleClass::create(['name' => 'testRole_2']);
        $roleClass::create(['name' => 'testRole_Team', 'team_test_id' => 1]);
        $roleClass::create(['name' => 'testRole_Team', 'team_test_id' => 2]); // same name different team
        Artisan::call('permission:show', [
            '--permission-registrar' => $permissionRegistrarAbstract,
        ]);

        $output = Artisan::output();

        // |    | Team ID: NULL         | Team ID: 1    | Team ID: 2    |
        // |    | testRole | testRole_2 | testRole_Team | testRole_Team |
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('/\|\s+\|\s+Team ID: NULL\s+\|\s+Team ID: 1\s+\|\s+Team ID: 2\s+\|/', $output);
            $this->assertMatchesRegularExpression('/\|\s+\|\s+testRole_2\s+\|\s+testRole_Team\s+\|\s+testRole_Team\s+\|/', $output);
        } else { // phpUnit 9/8
            $this->assertRegExp('/\|\s+\|\s+Team ID: NULL\s+\|\s+Team ID: 1\s+\|\s+Team ID: 2\s+\|/', $output);
            $this->assertRegExp('/\|\s+\|\s+testRole_2\s+\|\s+testRole_Team\s+\|\s+testRole_Team\s+\|/', $output);
        }
    }
}
