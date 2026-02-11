<?php

use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Exceptions\PermissionAlreadyExists;
use Spatie\Permission\Tests\TestSupport\TestModels\User;

it('gets user models using with', function () {
    $this->testUser->givePermissionTo($this->testUserPermission);

    $permission = app(Permission::class)::with('users')
        ->where($this->testUserPermission->getKeyName(), $this->testUserPermission->getKey())
        ->first();

    expect($this->testUserPermission->getKey())->toEqual($permission->getKey());
    expect($permission->users)->toHaveCount(1);
    expect($this->testUser->id)->toEqual($permission->users[0]->id);
});

it('throws an exception when the permission already exists', function () {
    app(Permission::class)->create(['name' => 'test-permission']);

    expect(fn () => app(Permission::class)->create(['name' => 'test-permission']))
        ->toThrow(PermissionAlreadyExists::class);
});

it('belongs to a guard', function () {
    $permission = app(Permission::class)->create(['name' => 'can-edit', 'guard_name' => 'admin']);

    expect($permission->guard_name)->toEqual('admin');
});

it('belongs to the default guard by default', function () {
    expect($this->testUserPermission->guard_name)->toEqual(
        $this->app['config']->get('auth.defaults.guard')
    );
});

it('has user models of the right class', function () {
    $this->testAdmin->givePermissionTo($this->testAdminPermission);

    $this->testUser->givePermissionTo($this->testUserPermission);

    expect($this->testUserPermission->users)->toHaveCount(1);
    expect($this->testUserPermission->users->first()->is($this->testUser))->toBeTrue();
    expect($this->testUserPermission->users->first())->toBeInstanceOf(User::class);
});

it('is retrievable by id', function () {
    $permission_by_id = app(Permission::class)->findById($this->testUserPermission->id);

    expect($permission_by_id->id)->toEqual($this->testUserPermission->id);
});

it('can delete hydrated permissions', function () {
    $this->reloadPermissions();

    $permission = app(Permission::class)->findByName($this->testUserPermission->name);
    $permission->delete();

    expect(app(Permission::class)->where($this->testUserPermission->getKeyName(), $this->testUserPermission->getKey())->get())->toHaveCount(0);
});
