<?php

namespace Spatie\Permission\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Tests\TestModels\User;

function assertQueryCount(int $expected): void
{
    expect(DB::getQueryLog())->toHaveCount($expected);
}

function resetQueryCount(): void
{
    DB::flushQueryLog();
}

beforeEach(function () {
    $this->cache_init_count = 0;

    $this->cache_load_count = 0;

    $this->cache_run_count = 2; // roles lookup, permissions lookup

    $this->cache_relations_count = 1;

    $this->registrar = app(PermissionRegistrar::class);

    $this->registrar->forgetCachedPermissions();

    DB::connection()->enableQueryLog();

    $cacheStore = $this->registrar->getCacheStore();

    switch (true) {
        case $cacheStore instanceof \Illuminate\Cache\DatabaseStore:
        $this->cache_init_count = 1;
        $this->cache_load_count = 1;
        // no break
        default:
    }
});

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

it('removing a permission from a user should not flush the cache', function () {
    $this->testUser->givePermissionTo('edit-articles');

    $this->registrar->getPermissions();

    $this->testUser->revokePermissionTo('edit-articles');

    resetQueryCount();

    $this->registrar->getPermissions();

    assertQueryCount(0);
});

it('removing a role from a user should not flush the cache', function () {
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

it('flushes the cache when assign a permission to a role', function () {
    $this->testUserRole->givePermissionTo('edit-articles');

    resetQueryCount();

    $this->registrar->getPermissions();

    assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
});

it('user creation should not flush the cache', function () {
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

it('has permission to should use the cache', function () {
    $this->testUserRole->givePermissionTo(['edit-articles', 'edit-news', 'Edit News']);
    $this->testUser->assignRole('testRole');

    resetQueryCount();
    expect($this->testUser->hasPermissionTo('edit-articles'))->toBeTrue();
    assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count + $this->cache_relations_count);

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

it('the cache should differentiate by guard name', function () {
    $this->testUserRole->givePermissionTo(['edit-articles', 'web']);
    $this->testUser->assignRole('testRole');

    resetQueryCount();
    expect($this->testUser->hasPermissionTo('edit-articles', 'web'))->toBeTrue();
    assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count + $this->cache_relations_count);

    resetQueryCount();
    expect($this->testUser->hasPermissionTo('edit-articles', 'admin'))->toBeFalse();
    assertQueryCount(1); // 1 for first lookup of this permission with this guard
})->throws(PermissionDoesNotExist::class);

it('get all permissions should use the cache', function () {
    $this->testUserRole->givePermissionTo($expected = ['edit-articles', 'edit-news']);
    $this->testUser->assignRole('testRole');

    resetQueryCount();
    $this->registrar->getPermissions();
    assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);

    resetQueryCount();
    $actual = $this->testUser->getAllPermissions()->pluck('name')->sort()->values();
    expect(collect($expected))->toEqual($actual);

    assertQueryCount(2);
});

it('get all permissions should not over hydrate roles', function () {
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
