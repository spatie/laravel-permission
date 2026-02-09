<?php

use Illuminate\Cache\DatabaseStore;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Tests\TestSupport\TestModels\User;

beforeEach(function () {
    $this->registrar = app(PermissionRegistrar::class);

    $this->registrar->forgetCachedPermissions();

    DB::connection()->enableQueryLog();

    $this->cache_init_count = 0;
    $this->cache_load_count = 0;
    $this->cache_run_count = 2; // roles lookup, permissions lookup

    $cacheStore = $this->registrar->getCacheStore();

    switch (true) {
        case $cacheStore instanceof DatabaseStore:
            $this->cache_init_count = 1;
            $this->cache_load_count = 1;
            // no break
        default:
    }
});

function resetQueryCount(): void
{
    DB::flushQueryLog();
}

function assertQueryCount(int $expected): void
{
    expect(DB::getQueryLog())->toHaveCount($expected);
}

it('can cache the permissions', function () {
    resetQueryCount();

    $this->registrar->getPermissions();

    assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
});

it('flushes the cache when creating a permission', function () {
    app(Permission::class)->create(['name' => 'new']);

    resetQueryCount();

    $this->registrar->getPermissions();

    assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
});

it('flushes the cache when updating a permission', function () {
    $permission = app(Permission::class)->create(['name' => 'new']);

    $permission->name = 'other name';
    $permission->save();

    resetQueryCount();

    $this->registrar->getPermissions();

    assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
});

it('flushes the cache when creating a role', function () {
    app(Role::class)->create(['name' => 'new']);

    resetQueryCount();

    $this->registrar->getPermissions();

    assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
});

it('flushes the cache when updating a role', function () {
    $role = app(Role::class)->create(['name' => 'new']);

    $role->name = 'other name';
    $role->save();

    resetQueryCount();

    $this->registrar->getPermissions();

    assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
});

it('should not flush the cache when removing a permission from a user', function () {
    $this->testUser->givePermissionTo('edit-articles');

    $this->registrar->getPermissions();

    $this->testUser->revokePermissionTo('edit-articles');

    resetQueryCount();

    $this->registrar->getPermissions();

    assertQueryCount(0);
});

it('should not flush the cache when removing a role from a user', function () {
    $this->testUser->assignRole('testRole');

    $this->registrar->getPermissions();

    $this->testUser->removeRole('testRole');

    resetQueryCount();

    $this->registrar->getPermissions();

    assertQueryCount(0);
});

it('flushes the cache when removing a role from a permission', function () {
    $this->testUserPermission->assignRole('testRole');

    $this->registrar->getPermissions();

    $this->testUserPermission->removeRole('testRole');

    resetQueryCount();

    $this->registrar->getPermissions();

    assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
});

it('flushes the cache when assigning a permission to a role', function () {
    $this->testUserRole->givePermissionTo('edit-articles');

    resetQueryCount();

    $this->registrar->getPermissions();

    assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
});

it('should not flush the cache on user creation', function () {
    $this->registrar->getPermissions();

    User::create(['email' => 'new']);

    resetQueryCount();

    $this->registrar->getPermissions();

    // should all be in memory, so no init/load required
    assertQueryCount(0);
});

it('flushes the cache when giving a permission to a role', function () {
    $this->testUserRole->givePermissionTo($this->testUserPermission);

    resetQueryCount();

    $this->registrar->getPermissions();

    assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
});

it('uses the cache for has permission to', function () {
    $this->testUserRole->givePermissionTo(['edit-articles', 'edit-news', 'Edit News']);
    $this->testUser->assignRole('testRole');
    $this->testUser->loadMissing('roles', 'permissions');

    resetQueryCount();
    expect($this->testUser->hasPermissionTo('edit-articles'))->toBeTrue();
    assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);

    resetQueryCount();
    expect($this->testUser->hasPermissionTo('edit-news'))->toBeTrue();
    assertQueryCount(0);

    resetQueryCount();
    expect($this->testUser->hasPermissionTo('edit-articles'))->toBeTrue();
    assertQueryCount(0);

    resetQueryCount();
    expect($this->testUser->hasPermissionTo('Edit News'))->toBeTrue();
    assertQueryCount(0);
});

it('differentiates the cache by guard name', function () {
    $this->expectException(PermissionDoesNotExist::class);

    $this->testUserRole->givePermissionTo(['edit-articles', 'web']);
    $this->testUser->assignRole('testRole');
    $this->testUser->loadMissing('roles', 'permissions');

    resetQueryCount();
    expect($this->testUser->hasPermissionTo('edit-articles', 'web'))->toBeTrue();
    assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);

    resetQueryCount();
    expect($this->testUser->hasPermissionTo('edit-articles', 'admin'))->toBeFalse();
    assertQueryCount(1); // 1 for first lookup of this permission with this guard
});

it('uses the cache for get all permissions', function () {
    $this->testUserRole->givePermissionTo($expected = ['edit-articles', 'edit-news']);
    $this->testUser->assignRole('testRole');
    $this->testUser->loadMissing('roles.permissions', 'permissions');

    resetQueryCount();
    $this->registrar->getPermissions();
    assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);

    resetQueryCount();
    $actual = $this->testUser->getAllPermissions()->pluck('name')->sort()->values();
    expect($actual)->toEqual(collect($expected));

    assertQueryCount(0);
});

it('should not over hydrate roles for get all permissions', function () {
    $this->testUserRole->givePermissionTo(['edit-articles', 'edit-news']);
    $permissions = $this->registrar->getPermissions();
    $roles = $permissions->flatMap->roles;

    // Should have same object reference
    expect($roles[1])->toBe($roles[0]);
});

it('can reset the cache with artisan command', function () {
    Artisan::call('permission:create-permission', ['name' => 'new-permission']);
    expect(\Spatie\Permission\Models\Permission::where('name', 'new-permission')->get())->toHaveCount(1);

    resetQueryCount();
    // retrieve permissions, and assert that the cache had to be loaded
    $this->registrar->getPermissions();
    assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);

    // reset the cache
    Artisan::call('permission:cache-reset');

    resetQueryCount();
    $this->registrar->getPermissions();
    // assert that the cache had to be reloaded
    assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
});
