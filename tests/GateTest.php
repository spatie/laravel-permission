<?php

namespace Spatie\Permission\Tests;

use Illuminate\Contracts\Auth\Access\Gate;

it('can determine if a user does not have a permission', function () {
    expect($this->testUser->can('edit-articles'))->toBeFalse();
});

it('allows other gate before callbacks to run if a user does not have a permission', function () {
    expect($this->testUser->can('edit-articles'))->toBeFalse();

    app(Gate::class)->before(function () {
        return true;
    });

    expect($this->testUser->can('edit-articles'))->toBeTrue();
});

it('can determine if a user has a direct permission', function () {
    $this->testUser->givePermissionTo('edit-articles');

    expect($this->testUser->can('edit-articles'))->toBeTrue();

    expect($this->testUser->can('non-existing-permission'))->toBeFalse();

    expect($this->testUser->can('admin-permission'))->toBeFalse();
});

it('can determine if a user has a permission through roles', function () {
    $this->testUserRole->givePermissionTo($this->testUserPermission);

    $this->testUser->assignRole($this->testUserRole);

    expect($this->testUser->hasPermissionTo($this->testUserPermission))->toBeTrue();

    expect($this->testUser->can('edit-articles'))->toBeTrue();

    expect($this->testUser->can('non-existing-permission'))->toBeFalse();

    expect($this->testUser->can('admin-permission'))->toBeFalse();
});

it('can determine if a user with a different guard has a permission when using roles', function () {
    $this->testAdminRole->givePermissionTo($this->testAdminPermission);

    $this->testAdmin->assignRole($this->testAdminRole);

    expect($this->testAdmin->hasPermissionTo($this->testAdminPermission))->toBeTrue();

    expect($this->testAdmin->can('admin-permission'))->toBeTrue();

    expect($this->testAdmin->can('non-existing-permission'))->toBeFalse();

    expect($this->testAdmin->can('edit-articles'))->toBeFalse();
});
