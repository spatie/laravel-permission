<?php

namespace Spatie\Permission\Test;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Contracts\Permission;

class CacheTest extends TestCase
{
    protected $registrar;

    public function setUp()
    {
        parent::setUp();

        $this->registrar = app(PermissionRegistrar::class);

        $this->registrar->forgetCachedPermissions();

        DB::connection()->enableQueryLog();

        $this->assertCount(0, DB::getQueryLog());

        //cache was empty, some queries should have been performed
        $this->registrar->registerPermissions();

        $this->assertCount(2, DB::getQueryLog());
    }

    /** @test */
    public function it_can_cache_the_permissions()
    {
        //permission should be cached an no queries should be performed
        $this->registrar->registerPermissions();
        $this->assertCount(2, DB::getQueryLog());
    }

    /** @test */
    public function permission_creation_and_updating_should_flush_the_cache()
    {
        $permission = app(Permission::class)->create(['name' => 'new']);
        $this->assertCount(3, DB::getQueryLog());

        $this->registrar->registerPermissions();
        $this->assertCount(5, DB::getQueryLog());

        $permission->name = 'other name';
        $permission->save();
        $this->assertCount(6, DB::getQueryLog());

        $this->registrar->registerPermissions();
        $this->assertCount(8, DB::getQueryLog());
    }

    /** @test */
    public function role_creation_and_updating_should_flush_the_cache()
    {
        $role = app(Role::class)->create(['name' => 'new']);
        $this->assertCount(3, DB::getQueryLog());

        $this->registrar->registerPermissions();
        $this->assertCount(5, DB::getQueryLog());

        $role->name = 'other name';
        $role->save();
        $this->assertCount(6, DB::getQueryLog());

        $this->registrar->registerPermissions();
        $this->assertCount(8, DB::getQueryLog());
    }

    /** @test */
    public function user_creation_should_not_flush_the_cache()
    {
        User::create(['email' => 'new']);
        $this->assertCount(3, DB::getQueryLog());

        $this->registrar->registerPermissions();
        $this->assertCount(5, DB::getQueryLog());

        $this->registrar->registerPermissions();
        $this->assertCount(5, DB::getQueryLog());
    }

    /** @test */
    public function adding_a_permission_to_a_role_should_flush_the_cache()
    {
        $this->testRole->givePermissionTo($this->testPermission);
        $this->assertCount(3, DB::getQueryLog());

        $this->registrar->registerPermissions();
        $this->assertCount(5, DB::getQueryLog());
    }

    /** @test */
    public function has_permission_to_should_use_the_cache()
    {
        $this->testRole->givePermissionTo(['edit-articles', 'edit-news']);
        $this->testUser->assignRole('testRole');
        $this->assertCount(6, DB::getQueryLog());

        $this->assertTrue($this->testUser->hasPermissionTo('edit-articles'));
        $this->assertCount(10, DB::getQueryLog());

        $this->assertTrue($this->testUser->hasPermissionTo('edit-news'));
        $this->assertCount(10, DB::getQueryLog());

        $this->assertTrue($this->testUser->hasPermissionTo('edit-articles'));
        $this->assertCount(10, DB::getQueryLog());
    }
}
