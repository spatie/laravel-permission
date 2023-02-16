<?php

namespace Spatie\Permission\Tests;

use Spatie\Permission\Models\Permission;

beforeEach(function () {
    app('config')->set('permission.enable_wildcard_permission', true);

    Permission::create(['name' => 'other-permission']);

    Permission::create(['name' => 'wrong-guard-permission', 'guard_name' => 'admin']);
});

it('can be given a permission', function () {
    Permission::create(['name' => 'posts.*']);
    $this->testUserRole->givePermissionTo('posts.*');

    expect($this->testUserRole->hasPermissionTo('posts.create'))->toBeTrue();
});

it('can be given multiple permissions using an array', function () {
    Permission::create(['name' => 'posts.*']);
    Permission::create(['name' => 'news.*']);

    $this->testUserRole->givePermissionTo(['posts.*', 'news.*']);

    expect($this->testUserRole->hasPermissionTo('posts.create'))->toBeTrue();
    expect($this->testUserRole->hasPermissionTo('news.create'))->toBeTrue();
});

it('can be given multiple permissions using multiple arguments', function () {
    Permission::create(['name' => 'posts.*']);
    Permission::create(['name' => 'news.*']);

    $this->testUserRole->givePermissionTo('posts.*', 'news.*');

    expect($this->testUserRole->hasPermissionTo('posts.edit.123'))->toBeTrue();
    expect($this->testUserRole->hasPermissionTo('news.view.1'))->toBeTrue();
});

it('can be given a permission using objects', function () {
    $this->testUserRole->givePermissionTo($this->testUserPermission);

    expect($this->testUserRole->hasPermissionTo($this->testUserPermission))->toBeTrue();
});

it('returns false if it does not have the permission', function () {
    expect($this->testUserRole->hasPermissionTo('other-permission'))->toBeFalse();
});

it('returns false if permission does not exists', function () {
    expect($this->testUserRole->hasPermissionTo('doesnt-exist'))->toBeFalse();
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

it('returns false when a permission of the wrong guard is passed in', function () {
    $permission = app(Permission::class)->findByName('wrong-guard-permission', 'admin');

    expect($this->testUserRole->hasPermissionTo($permission))->toBeFalse();
});
