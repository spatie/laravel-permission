<?php

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Tests\TestSupport\TestModels\User;

/*
 * The permission cache is one snapshot shared by every request. These tests build it
 * in one "request" and read it in another, with different global scopes active.
 */

beforeEach(function () {
    $this->registeredScopes = [];

    $this->addScope = function (string $contract, string $name, Closure $scope): void {
        $model = app($contract)::class;
        $model::addGlobalScope($name, $scope);

        $this->registeredScopes[] = [$model, $name];
    };

    $this->removeScopes = function (): void {
        $scopes = Model::getAllGlobalScopes();

        foreach ($this->registeredScopes as [$model, $name]) {
            unset($scopes[$model][$name]);
        }

        Model::setAllGlobalScopes($scopes);
        $this->registeredScopes = [];
    };

    $this->rebuildCache = function (): void {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        app(PermissionRegistrar::class)->getPermissions();
    };

    // A later request starts without the in-memory collection and without loaded relations.
    $this->userInNextRequest = function (): User {
        app(PermissionRegistrar::class)->clearPermissionsCollection();

        return User::query()->findOrFail($this->testUser->getKey());
    };

    $this->giveBothRolesThePermission = function (): void {
        app(Role::class)->findByName('testRole')->givePermissionTo('edit-articles');
        app(Role::class)->findByName('testRole2')->givePermissionTo('edit-articles');
    };
});

afterEach(fn () => ($this->removeScopes)());

it('grants a permission when the cache was built under a role scope of another request', function () {
    ($this->giveBothRolesThePermission)();
    $this->testUser->assignRole('testRole2');

    ($this->addScope)(Role::class, 'tenant', fn ($query) => $query->where('name', 'testRole'));
    ($this->rebuildCache)();
    ($this->removeScopes)();

    $user = ($this->userInNextRequest)();

    expect($user->hasPermissionTo('edit-articles'))->toBeTrue();
    expect($user->can('edit-articles'))->toBeTrue();
    expect(User::permission('edit-articles')->pluck('id')->all())->toBe([$user->id]);
});

it('grants a permission to another team when the cache was built under a role scope of one team', function () {
    $this->setUpTeams();

    setPermissionsTeamId(1);
    $roleOfTeamOne = app(Role::class)->create(['name' => 'editor', 'team_test_id' => 1]);
    $roleOfTeamOne->givePermissionTo('edit-articles');

    setPermissionsTeamId(2);
    $roleOfTeamTwo = app(Role::class)->create(['name' => 'editor', 'team_test_id' => 2]);
    $roleOfTeamTwo->givePermissionTo('edit-articles');
    $this->testUser->assignRole($roleOfTeamTwo);

    setPermissionsTeamId(1);
    ($this->addScope)(Role::class, 'tenant', fn ($query) => $query->where('team_test_id', 1));
    ($this->rebuildCache)();
    ($this->removeScopes)();

    setPermissionsTeamId(2);
    expect(($this->userInNextRequest)()->hasPermissionTo('edit-articles'))->toBeTrue();

    setPermissionsTeamId(1);
    expect(($this->userInNextRequest)()->hasPermissionTo('edit-articles'))->toBeFalse();
});

it('keeps applying a role scope of the current request', function () {
    ($this->giveBothRolesThePermission)();
    $this->testUser->assignRole('testRole2');

    // The cache is built without the scope, the check happens with it.
    ($this->rebuildCache)();
    ($this->addScope)(Role::class, 'restricted', fn ($query) => $query->where('name', '!=', 'testRole2'));

    $user = ($this->userInNextRequest)();

    expect($user->hasPermissionTo('edit-articles'))->toBeFalse();
    expect($user->can('edit-articles'))->toBeFalse();
    expect(User::permission('edit-articles')->count())->toBe(0);

    // The same holds when the cache is built with the scope.
    ($this->rebuildCache)();

    expect(($this->userInNextRequest)()->hasPermissionTo('edit-articles'))->toBeFalse();
});

it('does not grant a permission through a soft deleted role', function () {
    $this->setUpCustomModels();

    ($this->giveBothRolesThePermission)();
    $this->testUser->assignRole('testRole2');

    ($this->addScope)(Role::class, 'tenant', fn ($query) => $query->where('name', 'testRole'));
    app(Role::class)->withoutGlobalScope('tenant')->where('name', 'testRole2')->firstOrFail()->delete();
    ($this->rebuildCache)();

    $cachedRoles = app(PermissionRegistrar::class)
        ->getPermissions(['name' => 'edit-articles'])->first()->roles->pluck('name');

    expect($cachedRoles->all())->toBe(['testRole']);

    ($this->removeScopes)();

    expect(($this->userInNextRequest)()->hasPermissionTo('edit-articles'))->toBeFalse();
});

it('does not grant a soft deleted permission through a role', function () {
    $this->setUpCustomModels();

    ($this->giveBothRolesThePermission)();
    $this->testUser->assignRole('testRole2');

    app(Permission::class)->findByName('edit-articles')->delete();

    ($this->addScope)(Role::class, 'tenant', fn ($query) => $query->where('name', 'testRole'));
    ($this->rebuildCache)();
    ($this->removeScopes)();

    $user = ($this->userInNextRequest)();

    expect($user->checkPermissionTo('edit-articles'))->toBeFalse();
    expect($user->can('edit-articles'))->toBeFalse();
});

it('does not grant a permission hidden by a permission scope through a role', function () {
    ($this->giveBothRolesThePermission)();
    $this->testUser->assignRole('testRole2');

    ($this->addScope)(Permission::class, 'available', fn ($query) => $query->where('name', '!=', 'edit-articles'));
    ($this->rebuildCache)();

    $user = ($this->userInNextRequest)();

    expect($user->checkPermissionTo('edit-articles'))->toBeFalse();
    expect($user->can('edit-articles'))->toBeFalse();
});

it('leaves only the configured permission scopes out of the cache', function () {
    ($this->giveBothRolesThePermission)();
    $this->testUser->assignRole('testRole2');

    config(['permission.cache.without_global_scopes' => ['tenant']]);

    ($this->addScope)(Permission::class, 'tenant', fn ($query) => $query->where('name', 'edit-news'));
    ($this->rebuildCache)();
    ($this->removeScopes)();

    expect(($this->userInNextRequest)()->checkPermissionTo('edit-articles'))->toBeTrue();

    ($this->addScope)(Permission::class, 'tenant', fn ($query) => $query->where('name', 'edit-news'));
    ($this->addScope)(Permission::class, 'available', fn ($query) => $query->where('name', '!=', 'edit-articles'));
    ($this->rebuildCache)();
    ($this->removeScopes)();

    expect(($this->userInNextRequest)()->checkPermissionTo('edit-articles'))->toBeFalse();
});

it('lets a permission scope of another request filter the cache by default', function () {
    ($this->giveBothRolesThePermission)();
    $this->testUser->assignRole('testRole2');

    ($this->addScope)(Permission::class, 'tenant', fn ($query) => $query->where('name', 'edit-news'));
    ($this->rebuildCache)();
    ($this->removeScopes)();

    expect(($this->userInNextRequest)()->checkPermissionTo('edit-articles'))->toBeFalse();
});
