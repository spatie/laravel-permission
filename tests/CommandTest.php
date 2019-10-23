<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;

class CommandTest extends TestCase
{
    /** @test */
    public function it_can_create_a_role()
    {
        Artisan::call('permission:create-role', ['name' => 'new-role', 'company' => $this->company]);

        $this->assertCount(1, Role::where('name', 'new-role')->get());
        $this->assertCount(0, Role::where('name', 'new-role')->first()->permissions);
    }

    /** @test */
    public function it_can_create_a_role_with_a_specific_guard()
    {
        Artisan::call('permission:create-role', [
            'name' => 'new-role',
            'company' => $this->company,
            'guard' => 'api',
        ]);

        $this->assertCount(1, Role::where('name', 'new-role')
            ->where('guard_name', 'api')
            ->where('company', $this->company)
            ->get());
    }

    /** @test */
    public function it_can_create_a_permission()
    {
        Artisan::call('permission:create-permission', ['name' => 'new-permission', 'company' => $this->company]);

        $this->assertCount(1, Permission::where('name', 'new-permission')->where('company', $this->company)->get());
    }

    /** @test */
    public function it_can_create_a_permission_with_a_specific_guard()
    {
        Artisan::call('permission:create-permission', [
            'name' => 'new-permission',
            'guard' => 'api',
            'company' => $this->company
        ]);

        $this->assertCount(1, Permission::where('name', 'new-permission')
            ->where('guard_name', 'api')
            ->where('company', $this->company)
            ->get());
    }

    /** @test */
    public function it_can_create_a_role_and_permissions_at_same_time()
    {
        Artisan::call('permission:create-role', [
            'name' => 'new-role',
            'company' => $this->company,
            'permissions' => 'first permission | second permission',
        ]);

        $role = Role::where('name', 'new-role')->where('company', $this->company)->first();

        $this->assertTrue($role->hasPermissionTo('first permission'));
        $this->assertTrue($role->hasPermissionTo('second permission'));
    }

    /** @test */
    public function it_can_create_a_role_without_duplication()
    {
        Artisan::call('permission:create-role', ['name' => 'new-role', 'company' => $this->company]);
        Artisan::call('permission:create-role', ['name' => 'new-role', 'company' => $this->company]);

        $this->assertCount(1, Role::where('name', 'new-role')->where('company', $this->company)->get());
        $this->assertCount(0, Role::where('name', 'new-role')->where('company', $this->company)->first()->permissions);
    }

    /** @test */
    public function it_can_create_a_permission_without_duplication()
    {
        Artisan::call('permission:create-permission', ['name' => 'new-permission', 'company' => $this->company]);
        Artisan::call('permission:create-permission', ['name' => 'new-permission', 'company' => $this->company]);

        $this->assertCount(1, Permission::where('name', 'new-permission')->where('company', $this->company)->get());
    }

    /** @test */
    public function it_can_show_permission_tables()
    {
        Artisan::call('permission:show', ['company' => $this->company]);

        $output = Artisan::output();

        $this->assertTrue(strpos($output, 'Guard: web') !== false);
        $this->assertTrue(strpos($output, 'Guard: admin') !== false);

        // |               | testRole | testRole2 |
        $this->assertRegExp('/\|\s+\|\s+testRole\s+\|\s+testRole2\s+\|/', $output);

        // | edit-articles |  ·       |  ·        |
        $this->assertRegExp('/\|\s+edit-articles\s+\|\s+·\s+\|\s+·\s+\|/', $output);

        Role::findByName('testRole', $this->company)->givePermissionTo('edit-articles');
        $this->reloadPermissions();

        Artisan::call('permission:show', ['company' => $this->company]);

        $output = Artisan::output();

        // | edit-articles |  ·       |  ·        |
        $this->assertRegExp('/\|\s+edit-articles\s+\|\s+✔\s+\|\s+·\s+\|/', $output);
    }

    /** @test */
    public function it_can_show_permissions_for_guard()
    {
        Artisan::call('permission:show', ['guard' => 'web','company' => $this->company]);

        $output = Artisan::output();

        $this->assertTrue(strpos($output, 'Guard: web') !== false);
        $this->assertTrue(strpos($output, 'Guard: admin') === false);
    }
}
