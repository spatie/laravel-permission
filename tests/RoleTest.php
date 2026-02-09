<?php

use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Exceptions\RoleAlreadyExists;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Tests\TestCase;
use Spatie\Permission\Tests\TestModels\Admin;
use Spatie\Permission\Tests\TestModels\RuntimeRole;
use Spatie\Permission\Tests\TestModels\User;

uses(TestCase::class);

beforeEach(function () {
    Permission::create(['name' => 'other-permission']);
    Permission::create(['name' => 'wrong-guard-permission', 'guard_name' => 'admin']);
});

it('get user models using with', function () {
    $this->testUser->assignRole($this->testUserRole);

    $role = app(Role::class)::with('users')
        ->where($this->testUserRole->getKeyName(), $this->testUserRole->getKey())->first();

    expect($role->getKey())->toEqual($this->testUserRole->getKey());
    expect($role->users)->toHaveCount(1);
    expect($role->users[0]->id)->toEqual($this->testUser->id);
});

it('has user models of the right class', function () {
    $this->testAdmin->assignRole($this->testAdminRole);
    $this->testUser->assignRole($this->testUserRole);

    expect($this->testUserRole->users)->toHaveCount(1);
    expect($this->testUserRole->users->first()->is($this->testUser))->toBeTrue();
    expect($this->testUserRole->users->first())->toBeInstanceOf(User::class);

    expect($this->testAdminRole->users)->toHaveCount(1);
    expect($this->testAdminRole->users->first()->is($this->testAdmin))->toBeTrue();
    expect($this->testAdminRole->users->first())->toBeInstanceOf(Admin::class);
});

it('throws an exception when the role already exists', function () {
    $this->expectException(RoleAlreadyExists::class);

    app(Role::class)->create(['name' => 'test-role']);
    app(Role::class)->create(['name' => 'test-role']);
});

it('can be given a permission', function () {
    $this->testUserRole->givePermissionTo('edit-articles');

    expect($this->testUserRole->hasPermissionTo('edit-articles'))->toBeTrue();
});

it('throws an exception when given a permission that does not exist', function () {
    $this->expectException(PermissionDoesNotExist::class);

    $this->testUserRole->givePermissionTo('create-evil-empire');
});

it('throws an exception when given a permission that belongs to another guard', function () {
    $this->expectException(PermissionDoesNotExist::class);

    $this->testUserRole->givePermissionTo('admin-permission');

    $this->expectException(GuardDoesNotMatch::class);

    $this->testUserRole->givePermissionTo($this->testAdminPermission);
});

it('can be given multiple permissions using an array', function () {
    $this->testUserRole->givePermissionTo(['edit-articles', 'edit-news']);

    expect($this->testUserRole->hasPermissionTo('edit-articles'))->toBeTrue();
    expect($this->testUserRole->hasPermissionTo('edit-news'))->toBeTrue();
});

it('can be given multiple permissions using multiple arguments', function () {
    $this->testUserRole->givePermissionTo('edit-articles', 'edit-news');

    expect($this->testUserRole->hasPermissionTo('edit-articles'))->toBeTrue();
    expect($this->testUserRole->hasPermissionTo('edit-news'))->toBeTrue();
});

it('can sync permissions', function () {
    $this->testUserRole->givePermissionTo('edit-articles');

    $this->testUserRole->syncPermissions('edit-news');

    expect($this->testUserRole->hasPermissionTo('edit-articles'))->toBeFalse();
    expect($this->testUserRole->hasPermissionTo('edit-news'))->toBeTrue();
});

it('throws an exception when syncing permissions that do not exist', function () {
    $this->testUserRole->givePermissionTo('edit-articles');

    $this->expectException(PermissionDoesNotExist::class);

    $this->testUserRole->syncPermissions('permission-does-not-exist');
});

it('throws an exception when syncing permissions that belong to a different guard', function () {
    $this->testUserRole->givePermissionTo('edit-articles');

    $this->expectException(PermissionDoesNotExist::class);

    $this->testUserRole->syncPermissions('admin-permission');

    $this->expectException(GuardDoesNotMatch::class);

    $this->testUserRole->syncPermissions($this->testAdminPermission);
});

it('will remove all permissions when passing an empty array to sync permissions', function () {
    $this->testUserRole->givePermissionTo('edit-articles');
    $this->testUserRole->givePermissionTo('edit-news');

    $this->testUserRole->syncPermissions([]);

    expect($this->testUserRole->hasPermissionTo('edit-articles'))->toBeFalse();
    expect($this->testUserRole->hasPermissionTo('edit-news'))->toBeFalse();
});

test('sync permission error does not detach permissions', function () {
    $this->testUserRole->givePermissionTo('edit-news');

    $this->expectException(PermissionDoesNotExist::class);

    $this->testUserRole->syncPermissions('edit-articles', 'permission-that-does-not-exist');

    expect($this->testUserRole->fresh()->hasDirectPermission('edit-news'))->toBeTrue();
});

it('can revoke a permission', function () {
    $this->testUserRole->givePermissionTo('edit-articles');

    expect($this->testUserRole->hasPermissionTo('edit-articles'))->toBeTrue();

    $this->testUserRole->revokePermissionTo('edit-articles');

    $this->testUserRole = $this->testUserRole->fresh();

    expect($this->testUserRole->hasPermissionTo('edit-articles'))->toBeFalse();
});

it('can be given a permission using objects', function () {
    $this->testUserRole->givePermissionTo($this->testUserPermission);

    expect($this->testUserRole->hasPermissionTo($this->testUserPermission))->toBeTrue();
});

it('returns false if it does not have the permission', function () {
    expect($this->testUserRole->hasPermissionTo('other-permission'))->toBeFalse();
});

it('throws an exception if the permission does not exist', function () {
    $this->expectException(PermissionDoesNotExist::class);

    $this->testUserRole->hasPermissionTo('doesnt-exist');
});

it('returns false if it does not have a permission object', function () {
    $permission = app(Permission::class)->findByName('other-permission');

    expect($this->testUserRole->hasPermissionTo($permission))->toBeFalse();
});

it('creates permission object with findOrCreate if it does not have a permission object', function () {
    $permission = app(Permission::class)->findOrCreate('another-permission');

    expect($this->testUserRole->hasPermissionTo($permission))->toBeFalse();

    $this->testUserRole->givePermissionTo($permission);

    $this->testUserRole = $this->testUserRole->fresh();

    expect($this->testUserRole->hasPermissionTo('another-permission'))->toBeTrue();
});

it('creates a role with findOrCreate if the named role does not exist', function () {
    $this->expectException(RoleDoesNotExist::class);

    $role1 = app(Role::class)->findByName('non-existing-role');

    $this->assertNull($role1);

    $role2 = app(Role::class)->findOrCreate('yet-another-role');

    expect($role2)->toBeInstanceOf(Role::class);
});

it('throws an exception when a permission of the wrong guard is passed in', function () {
    $this->expectException(GuardDoesNotMatch::class);

    $permission = app(Permission::class)->findByName('wrong-guard-permission', 'admin');

    $this->testUserRole->hasPermissionTo($permission);
});

it('belongs to a guard', function () {
    $role = app(Role::class)->create(['name' => 'admin', 'guard_name' => 'admin']);

    expect($role->guard_name)->toEqual('admin');
});

it('belongs to the default guard by default', function () {
    expect($this->testUserRole->guard_name)->toEqual(
        $this->app['config']->get('auth.defaults.guard')
    );
});

it('can change role class on runtime', function () {
    $role = app(Role::class)->create(['name' => 'test-role-old']);
    expect($role)->not->toBeInstanceOf(RuntimeRole::class);

    $role->givePermissionTo('edit-articles');

    app('config')->set('permission.models.role', RuntimeRole::class);
    app()->bind(Role::class, RuntimeRole::class);
    app(PermissionRegistrar::class)->setRoleClass(RuntimeRole::class);

    $permission = app(Permission::class)->findByName('edit-articles');
    expect($permission->roles[0])->toBeInstanceOf(RuntimeRole::class);
    expect($permission->roles[0]->name)->toBe('test-role-old');

    $role = app(Role::class)->create(['name' => 'test-role']);
    expect($role)->toBeInstanceOf(RuntimeRole::class);

    $this->testUser->assignRole('test-role');
    expect($this->testUser->hasRole('test-role'))->toBeTrue();
    expect($this->testUser->roles[0])->toBeInstanceOf(RuntimeRole::class);
    expect($this->testUser->roles[0]->name)->toBe('test-role');
});
