<?php

use Illuminate\Contracts\Auth\Access\Gate;
use Spatie\Permission\Contracts\Permission;
it('can determine if a user does not have a permission', function () {
    expect($this->testUser->can('edit-articles'))->toBeFalse();
});

it('allows other gate before callbacks to run if a user does not have a permission', function () {
    expect($this->testUser->can('edit-articles'))->toBeFalse();

    app(Gate::class)->before(function () {
        // this Gate-before intercept overrides everything to true ... like a typical Super-Admin might use
        return true;
    });

    expect($this->testUser->can('edit-articles'))->toBeTrue();
});

it('allows gate after callback to grant denied privileges', function () {
    expect($this->testUser->can('edit-articles'))->toBeFalse();

    app(Gate::class)->after(function ($user, $ability, $result) {
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

it('can determine if a user has a direct permission using enums', function () {
    $enum = Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum::ViewArticles;

    $permission = app(Permission::class)->findOrCreate($enum->value, 'web');

    expect($this->testUser->can($enum->value))->toBeFalse();
    expect($this->testUser->canAny([$enum->value, 'some other permission']))->toBeFalse();

    $this->testUser->givePermissionTo($enum);

    expect($this->testUser->hasPermissionTo($enum))->toBeTrue();
    expect($this->testUser->can($enum->value))->toBeTrue();
    expect($this->testUser->canAny([$enum->value, 'some other permission']))->toBeTrue();
})->skip(PHP_VERSION_ID < 80100, 'Requires PHP >= 8.1');

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
