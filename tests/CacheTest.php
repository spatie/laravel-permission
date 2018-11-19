<?php

namespace Spatie\Permission\Test;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Contracts\Permission;

class CacheTest extends TestCase
{
    protected $cache_init_count = 0;
    protected $cache_load_count = 0;
    protected $cache_run_count = 2;
    protected $cache_reload_count = 0;
    protected $cache_untagged_count = 0;

    protected $registrar;

    public function setUp()
    {
        parent::setUp();

        $this->registrar = app(PermissionRegistrar::class);

        $this->registrar->forgetCachedPermissions();

        DB::connection()->enableQueryLog();

        $cacheStore = $this->registrar->getCacheStore();
        $taggable = $cacheStore instanceof \Illuminate\Cache\TaggableStore;

        switch (true) {
            case $cacheStore instanceof \Illuminate\Cache\DatabaseStore:
                $this->cache_init_count = 1;
                $this->cache_load_count = 1;
                $this->cache_reload_count = 1;
                $this->cache_untagged_count = -1;
                break;
            case $cacheStore instanceof \Illuminate\Cache\FileStore:
                $this->cache_untagged_count = -2;
                break;
            case $cacheStore instanceof \Illuminate\Cache\RedisStore:
                $this->cache_untagged_count = 0;
                break;
            case $cacheStore instanceof \Illuminate\Cache\MemcachedStore:
                $this->cache_untagged_count = 0;
                break;
            case $cacheStore instanceof \Illuminate\Cache\ArrayStore:
                $this->cache_untagged_count = 0;
            default:
        }
    }

    /** @test */
    public function it_can_cache_the_permissions()
    {
        $this->resetQueryCount();

        $this->registrar->getPermissions();

        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);

        $this->registrar->getPermissions();

        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count + $this->cache_reload_count);
    }

    /** @test */
    public function it_flushes_the_cache_when_creating_a_permission()
    {
        app(Permission::class)->create(['name' => 'new']);

        $this->resetQueryCount();

        $this->registrar->getPermissions();

        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
    }

    /** @test */
    public function it_flushes_the_cache_when_updating_a_permission()
    {
        $permission = app(Permission::class)->create(['name' => 'new']);

        $permission->name = 'other name';
        $permission->save();

        $this->resetQueryCount();

        $this->registrar->getPermissions();

        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
    }

    /** @test */
    public function it_flushes_the_cache_when_creating_a_role()
    {
        app(Role::class)->create(['name' => 'new']);

        $this->resetQueryCount();

        $this->registrar->getPermissions();

        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
    }

    /** @test */
    public function it_flushes_the_cache_when_updating_a_role()
    {
        $role = app(Role::class)->create(['name' => 'new']);

        $role->name = 'other name';
        $role->save();

        $this->resetQueryCount();

        $this->registrar->getPermissions();

        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
    }

    /** @test */
    public function user_creation_should_not_flush_the_cache()
    {
        $this->registrar->getPermissions();

        User::create(['email' => 'new']);

        $this->resetQueryCount();

        $this->registrar->getPermissions();

        $this->assertQueryCount($this->cache_init_count);
    }

    /** @test */
    public function it_flushes_the_cache_when_giving_a_permission_to_a_role()
    {
        $this->testUserRole->givePermissionTo($this->testUserPermission);

        $this->resetQueryCount();

        $this->registrar->getPermissions();

        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
    }

    /** @test */
    public function has_permission_to_should_use_the_cache()
    {
        $this->testUserRole->givePermissionTo(['edit-articles', 'edit-news']);
        $this->testUser->assignRole('testRole');

        $this->resetQueryCount();
        $this->assertTrue($this->testUser->hasPermissionTo('edit-articles'));
        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count + 1); // + 1 for getting the User's relations

        $this->resetQueryCount();
        $this->assertTrue($this->testUser->hasPermissionTo('edit-news'));
        $this->assertQueryCount($this->cache_run_count + $this->cache_untagged_count);

        $this->resetQueryCount();
        $this->assertTrue($this->testUser->hasPermissionTo('edit-articles'));
        $this->assertQueryCount($this->cache_init_count);
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
