<?php

namespace Spatie\Permission\Test;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\PermissionRegistrar;

class CacheModelsTest extends TestCase
{
    /** @var bool */
    protected $hasTeams = true;
    
    protected $cache_init_count = 0;
    protected $cache_load_count = 0;
    protected $cache_run_count = 0; // roles lookup, permissions lookup
    protected $cache_relations_count = 0;

    protected $registrar;

    public function setUp(): void
    {
        parent::setUp();

        $this->registrar = app(PermissionRegistrar::class);

        $this->registrar->forgetCachedPermissions();

        DB::connection()->enableQueryLog();

        $cacheStore = $this->registrar->getCacheStore();

        $this->resetQueryCount();

        switch (true) {
            case $cacheStore instanceof \Illuminate\Cache\DatabaseStore:
                $this->cache_init_count = 1;
                $this->cache_load_count = 1;
                // no break
            default:
                $cacheStore->flush();
        }
    }

    /** @test */
    public function it_can_cache_the_model_permissions()
    {
        $this->testAdmin->givePermissionTo($this->testAdminPermission);
        $this->assertTrue($this->testAdmin->hasAllDirectPermissions('admin-permission'));
        //new instances withour relations
        $testAdmin1 = Admin::find($this->testAdmin->id);
        $testAdmin2 = Admin::find($this->testAdmin->id);
        $testAdmin3 = Admin::find($this->testAdmin->id);

        $this->resetQueryCount();
        
        $this->assertCount(1, $this->testAdmin->permissions);
        $this->assertCount(1, $testAdmin1->permissions);
        $this->assertTrue($testAdmin1->hasAllDirectPermissions('admin-permission'));
        $this->assertTrue($testAdmin2->hasAllDirectPermissions('admin-permission'));
        $this->assertTrue($testAdmin3->hasAllDirectPermissions('admin-permission'));

        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
    }

    /** @test */
    public function it_can_cache_the_model_roles()
    {
        $this->testAdmin->assignRole($this->testAdminRole);
        $this->assertTrue($this->testAdmin->hasExactRoles('testAdminRole'));
        //new instances withour relations
        $testAdmin1 = Admin::find($this->testAdmin->id);
        $testAdmin2 = Admin::find($this->testAdmin->id);
        $testAdmin3 = Admin::find($this->testAdmin->id);

        $this->resetQueryCount();
        
        $this->assertCount(1, $this->testAdmin->roles);
        $this->assertCount(1, $testAdmin1->roles);
        $this->assertTrue($testAdmin1->hasExactRoles('testAdminRole'));
        $this->assertTrue($testAdmin2->hasExactRoles('testAdminRole'));
        $this->assertTrue($testAdmin3->hasExactRoles('testAdminRole'));

        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
    }

    /** @test */
    public function it_can_assign_same_and_different_roles_on_same_user_different_teams_with_cache()
    {
        app(Role::class)->create(['name' => 'testAdminRole2', 'guard_name' => 'admin']); //team_test_id = 1 by main class
        app(Role::class)->create(['name' => 'testAdminRole2', 'team_test_id' => 2, 'guard_name' => 'admin']);
        app(Role::class)->create(['name' => 'testAdminRole3', 'team_test_id' => null, 'guard_name' => 'admin']); //global role

        $this->setPermissionsTeamId(1);
        $this->testAdmin->assignRole('testAdminRole', 'testAdminRole2');
        $this->assertTrue($this->testAdmin->hasExactRoles(['testAdminRole', 'testAdminRole2']));
        
        $this->setPermissionsTeamId(2);
        $this->testAdmin->assignRole('testAdminRole2', 'testAdminRole3');
        $this->assertTrue($this->testAdmin->hasExactRoles(['testAdminRole2', 'testAdminRole3']));
        
        $this->resetQueryCount();
        $testAdmin1 = Admin::find($this->testAdmin->id); //new instance without relations
        $this->setPermissionsTeamId(1);        
        $this->assertTrue($testAdmin1->hasExactRoles(['testAdminRole', 'testAdminRole2']));        
        $this->setPermissionsTeamId(2);
        $this->assertTrue($testAdmin1->hasExactRoles(['testAdminRole2', 'testAdminRole3']));
        
        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count + 1);
    }

    public function it_can_assign_same_and_different_permission_on_same_user_on_different_teams_with_cache()
    {
        app(Permission::class)->create(['name' => 'admin-permission1', 'guard_name' => 'admin']);
        app(Permission::class)->create(['name' => 'admin-permission2', 'guard_name' => 'admin']);

        $this->setPermissionsTeamId(1);        
        $this->testAdmin->givePermissionTo('admin-permission', 'admin-permission1');
        $this->assertTrue($this->testAdmin->hasAllDirectPermissions('admin-permission', 'admin-permission1'));

        $this->setPermissionsTeamId(2);
        $this->testAdmin->givePermissionTo('admin-permission', 'admin-permission2');
        $this->assertTrue($this->testAdmin->hasAllDirectPermissions('admin-permission', 'admin-permission2'));
        $this->assertFalse($this->testAdmin->hasAllDirectPermissions('admin-permission1'));

        $this->resetQueryCount();
        $testAdmin1 = Admin::find($this->testAdmin->id); //new instance without relations
        $this->setPermissionsTeamId(1);
        $this->assertTrue($testAdmin1->hasAllDirectPermissions('admin-permission', 'admin-permission1'));
        $this->setPermissionsTeamId(2);
        $this->assertTrue($testAdmin1->hasExactRoles(['testAdminRole2', 'testAdminRole3']));
        $this->assertTrue($testAdmin1->hasAllDirectPermissions('admin-permission', 'admin-permission2'));

        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count + 1);
    }

    protected function assertQueryCount(int $expected)
    {
        $this->assertCount($expected, DB::getQueryLog());
    }

    protected function resetQueryCount()
    {
        DB::flushQueryLog();
    }
}
