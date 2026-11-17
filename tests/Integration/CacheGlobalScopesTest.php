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

describe('by default', function () {
    it('lets a role scope filter the cache', function () {
        ($this->giveBothRolesThePermission)();
        $this->testUser->assignRole('testRole2');

        ($this->addScope)(Role::class, 'tenant', fn ($query) => $query->where('name', 'testRole'));
        ($this->rebuildCache)();
        ($this->removeScopes)();

        expect(($this->userInNextRequest)()->hasPermissionTo('edit-articles'))->toBeFalse();
    });

    it('lets a role scope deny a user whose roles were already loaded', function () {
        app(Role::class)->findByName('testRole')->givePermissionTo('edit-articles');
        $this->testUser->assignRole('testRole');

        $user = User::query()->with('roles')->findOrFail($this->testUser->getKey());

        ($this->addScope)(Role::class, 'restricted', fn ($query) => $query->where('name', '!=', 'testRole'));
        ($this->rebuildCache)();
        app(PermissionRegistrar::class)->clearPermissionsCollection();

        expect($user->can('edit-articles'))->toBeFalse();
    });

    it('lets a permission scope filter the cache', function () {
        ($this->giveBothRolesThePermission)();
        $this->testUser->assignRole('testRole2');

        ($this->addScope)(Permission::class, 'tenant', fn ($query) => $query->where('name', 'edit-news'));
        ($this->rebuildCache)();
        ($this->removeScopes)();

        expect(($this->userInNextRequest)()->checkPermissionTo('edit-articles'))->toBeFalse();
    });
});

describe('with configured scopes', function () {
    it('leaves a listed role scope out of the cache', function () {
        config(['permission.cache.without_global_scopes.role' => ['tenant']]);

        ($this->giveBothRolesThePermission)();
        $this->testUser->assignRole('testRole2');

        ($this->addScope)(Role::class, 'tenant', fn ($query) => $query->where('name', 'testRole'));
        ($this->rebuildCache)();
        ($this->removeScopes)();

        $user = ($this->userInNextRequest)();

        expect($user->hasPermissionTo('edit-articles'))->toBeTrue();
        expect($user->can('edit-articles'))->toBeTrue();
    });

    it('leaves a listed role scope of one team out of the cache for another team', function () {
        $this->setUpTeams();
        config(['permission.cache.without_global_scopes.role' => ['tenant']]);

        setPermissionsTeamId(1);
        app(Role::class)->create(['name' => 'editor', 'team_test_id' => 1])->givePermissionTo('edit-articles');

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

    it('keeps applying role scopes that are not listed', function () {
        config(['permission.cache.without_global_scopes.role' => ['tenant']]);

        app(Role::class)->findByName('testRole')->givePermissionTo('edit-articles');
        $this->testUser->assignRole('testRole');

        $user = User::query()->with('roles')->findOrFail($this->testUser->getKey());

        ($this->addScope)(Role::class, 'tenant', fn ($query) => $query->where('name', 'testRole2'));
        ($this->addScope)(Role::class, 'restricted', fn ($query) => $query->where('name', '!=', 'testRole'));
        ($this->rebuildCache)();
        app(PermissionRegistrar::class)->clearPermissionsCollection();

        expect($user->can('edit-articles'))->toBeFalse();
    });

    it('leaves a listed permission scope out of the cache', function () {
        config(['permission.cache.without_global_scopes.permission' => ['tenant']]);

        ($this->giveBothRolesThePermission)();
        $this->testUser->assignRole('testRole2');

        ($this->addScope)(Permission::class, 'tenant', fn ($query) => $query->where('name', 'edit-news'));
        ($this->rebuildCache)();
        ($this->removeScopes)();

        expect(($this->userInNextRequest)()->checkPermissionTo('edit-articles'))->toBeTrue();
    });

    it('keeps applying permission scopes that are not listed', function () {
        config(['permission.cache.without_global_scopes.permission' => ['tenant']]);

        ($this->giveBothRolesThePermission)();
        $this->testUser->assignRole('testRole2');

        ($this->addScope)(Permission::class, 'tenant', fn ($query) => $query->where('name', 'edit-news'));
        ($this->addScope)(Permission::class, 'available', fn ($query) => $query->where('name', '!=', 'edit-articles'));
        ($this->rebuildCache)();
        ($this->removeScopes)();

        expect(($this->userInNextRequest)()->checkPermissionTo('edit-articles'))->toBeFalse();
    });

    it('keeps soft deletes applied', function () {
        $this->setUpCustomModels();
        config([
            'permission.cache.without_global_scopes.permission' => ['tenant'],
            'permission.cache.without_global_scopes.role' => ['tenant'],
        ]);

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
});
