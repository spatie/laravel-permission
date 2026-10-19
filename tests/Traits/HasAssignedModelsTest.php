<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Support\Config;
use Spatie\Permission\Tests\TestSupport\TestModels\Admin;
use Spatie\Permission\Tests\TestSupport\TestModels\User;

it('can sync models to a role', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    $this->testUserRole->syncModels([$user1, $user2]);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeTrue();
    expect($user2->fresh()->hasRole($this->testUserRole))->toBeTrue();
});

it('removes previous models when syncing', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    $user1->assignRole($this->testUserRole);
    expect($user1->fresh()->hasRole($this->testUserRole))->toBeTrue();

    $this->testUserRole->syncModels([$user2]);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeFalse();
    expect($user2->fresh()->hasRole($this->testUserRole))->toBeTrue();
});

it('removes all models when syncing with an empty array', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    $user1->assignRole($this->testUserRole);
    $user2->assignRole($this->testUserRole);

    $this->testUserRole->syncModels([]);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeFalse();
    expect($user2->fresh()->hasRole($this->testUserRole))->toBeFalse();
});

it('does not add duplicate models when syncing', function () {
    $user1 = User::create(['email' => 'user1@test.com']);

    $this->testUserRole->syncModels([$user1, $user1]);

    $count = DB::table(Config::modelHasRolesTable())
        ->where(app(PermissionRegistrar::class)->pivotRole, $this->testUserRole->getKey())
        ->count();

    expect($count)->toBe(1);
});

it('can sync models using a single model instance', function () {
    $user1 = User::create(['email' => 'user1@test.com']);

    $this->testUserRole->syncModels($user1);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeTrue();
});

it('can sync models using IDs', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    $this->testUserRole->syncModels([$user1->getKey(), $user2->getKey()]);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeTrue();
    expect($user2->fresh()->hasRole($this->testUserRole))->toBeTrue();
});

it('can assign a role to models', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    $this->testUserRole->assignToModels([$user1, $user2]);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeTrue();
    expect($user2->fresh()->hasRole($this->testUserRole))->toBeTrue();
});

it('can assign a role to a single model instance', function () {
    $user1 = User::create(['email' => 'user1@test.com']);

    $this->testUserRole->assignToModels($user1);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeTrue();
});

it('can assign a role to models using IDs', function () {
    $user1 = User::create(['email' => 'user1@test.com']);

    $this->testUserRole->assignToModels($user1->getKey());

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeTrue();
});

it('does not assign duplicate models', function () {
    $user1 = User::create(['email' => 'user1@test.com']);

    $this->testUserRole->assignToModels([$user1, $user1]);

    $count = DB::table(Config::modelHasRolesTable())
        ->where(app(PermissionRegistrar::class)->pivotRole, $this->testUserRole->getKey())
        ->count();

    expect($count)->toBe(1);
});

it('does not re-assign models already assigned', function () {
    $user1 = User::create(['email' => 'user1@test.com']);

    $this->testUserRole->assignToModels($user1);
    $this->testUserRole->assignToModels($user1);

    $count = DB::table(Config::modelHasRolesTable())
        ->where(app(PermissionRegistrar::class)->pivotRole, $this->testUserRole->getKey())
        ->count();

    expect($count)->toBe(1);
});

it('can assign additional models without removing existing ones', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    $this->testUserRole->assignToModels($user1);
    $this->testUserRole->assignToModels($user2);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeTrue();
    expect($user2->fresh()->hasRole($this->testUserRole))->toBeTrue();
});

it('can remove a role from models', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    $user1->assignRole($this->testUserRole);
    $user2->assignRole($this->testUserRole);

    $this->testUserRole->removeFromModels([$user1]);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeFalse();
    expect($user2->fresh()->hasRole($this->testUserRole))->toBeTrue();
});

it('can remove a role from a single model instance', function () {
    $user1 = User::create(['email' => 'user1@test.com']);

    $user1->assignRole($this->testUserRole);

    $this->testUserRole->removeFromModels($user1);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeFalse();
});

it('can remove a role from models using IDs', function () {
    $user1 = User::create(['email' => 'user1@test.com']);

    $user1->assignRole($this->testUserRole);

    $this->testUserRole->removeFromModels($user1->getKey());

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeFalse();
});

it('does nothing when removing the role from models that do not have it', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    $user1->assignRole($this->testUserRole);

    $this->testUserRole->removeFromModels($user2);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeTrue();
});

it('can sync models using IDs with explicit model class', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    $this->testUserRole->syncModels([$user1->getKey(), $user2->getKey()], User::class);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeTrue();
    expect($user2->fresh()->hasRole($this->testUserRole))->toBeTrue();
});

it('can assign a role to models using IDs with explicit model class', function () {
    $user1 = User::create(['email' => 'user1@test.com']);

    $this->testUserRole->assignToModels($user1->getKey(), User::class);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeTrue();
});

it('can remove a role from models using IDs with explicit model class', function () {
    $user1 = User::create(['email' => 'user1@test.com']);

    $user1->assignRole($this->testUserRole);

    $this->testUserRole->removeFromModels($user1->getKey(), User::class);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeFalse();
});

it('does nothing when assigning an unsaved role to models', function () {
    $role = new Role(['name' => 'unsaved-role']);
    $user1 = User::create(['email' => 'user1@test.com']);

    $result = $role->assignToModels($user1);

    expect($result)->toBe($role);
    expect($user1->fresh()->roles)->toHaveCount(0);
});

it('applies the current team id when assigning models with teams enabled', function () {
    $this->setUpTeams();

    $user1 = User::create(['email' => 'team-user1@test.com']);

    $this->testUserRole->assignToModels($user1);

    $pivot = DB::table(Config::modelHasRolesTable())
        ->where(Config::morphKey(), $user1->getKey())
        ->first();

    expect((int) $pivot->team_test_id)->toBe(1);
});

it('only syncs models for the active team', function (bool $replace) {
    $this->setUpTeams();

    $user = User::create(['email' => 'team-user@test.com']);
    $replacement = User::create(['email' => 'replacement@test.com']);
    $otherRole = Role::findByName('testRole2');

    setPermissionsTeamId(1);
    $user->assignRole($this->testUserRole, $otherRole);

    setPermissionsTeamId(2);
    $user->assignRole($this->testUserRole);

    setPermissionsTeamId(1);
    $this->testUserRole->syncModels($replace ? [$replacement] : []);

    expect($user->fresh()->hasRole($this->testUserRole))->toBeFalse();
    expect($user->fresh()->hasRole($otherRole))->toBeTrue();
    expect($replacement->fresh()->hasRole($this->testUserRole))->toBe($replace);

    setPermissionsTeamId(2);
    expect($user->fresh()->hasRole($this->testUserRole))->toBeTrue();
    expect($replacement->fresh()->hasRole($this->testUserRole))->toBeFalse();
})->with([false, true]);

it('assigns the same global role independently in each team without duplicates', function (bool $customModels) {
    $this->setUpTeams();

    if ($customModels) {
        setPermissionsTeamId(null);
        $this->setUpCustomModels();
        setPermissionsTeamId(1);
    }

    $this->testUserRole->assignToModels($this->testUser);

    setPermissionsTeamId(2);
    $this->testUserRole->assignToModels($this->testUser);
    $this->testUserRole->assignToModels($this->testUser);

    expect($this->testUser->fresh()->hasRole($this->testUserRole))->toBeTrue();

    setPermissionsTeamId(1);
    expect($this->testUser->fresh()->hasRole($this->testUserRole))->toBeTrue();

    expect(DB::table(Config::modelHasRolesTable())->count())->toBe(2);
})->with([false, true]);

it('only detaches the requested models and role in the active team', function () {
    $this->setUpTeams();

    $otherRole = Role::findByName('testRole2');
    $otherUser = User::create(['email' => 'other@test.com']);
    $this->testUser->assignRole($this->testUserRole, $otherRole);
    $otherUser->assignRole($this->testUserRole);
    $this->testUserRole->assignToModels($this->testAdmin);

    setPermissionsTeamId(2);
    $this->testUser->assignRole($this->testUserRole);

    setPermissionsTeamId(1);
    $this->testUserRole->removeFromModels($this->testUser);

    expect($this->testUser->fresh()->hasRole($this->testUserRole))->toBeFalse();
    expect($this->testUser->fresh()->hasRole($otherRole))->toBeTrue();
    expect($otherUser->fresh()->hasRole($this->testUserRole))->toBeTrue();
    expect($this->testAdmin->fresh()->hasRole($this->testUserRole))->toBeTrue();

    setPermissionsTeamId(2);
    expect($this->testUser->fresh()->hasRole($this->testUserRole))->toBeTrue();
});

it('rolls back all sync changes when an insert fails inside an existing transaction', function (bool $teams) {
    if ($teams) {
        $this->setUpTeams();
        setPermissionsTeamId(2);
        $this->testUser->assignRole($this->testUserRole);
        setPermissionsTeamId(1);
    }

    $this->testUser->assignRole($this->testUserRole);
    $replacement = User::create(['email' => 'replacement@test.com']);

    DB::transaction(function () use ($replacement) {
        expect(fn () => $this->testUserRole->syncModels([$replacement, new Admin]))
            ->toThrow(QueryException::class);

        expect(DB::transactionLevel())->toBe(1);
        expect($this->testUser->fresh()->hasRole($this->testUserRole))->toBeTrue();
        expect($replacement->fresh()->hasRole($this->testUserRole))->toBeFalse();
    });

    expect($this->testUser->fresh()->hasRole($this->testUserRole))->toBeTrue();
    expect($replacement->fresh()->hasRole($this->testUserRole))->toBeFalse();

    if ($teams) {
        setPermissionsTeamId(2);
        expect($this->testUser->fresh()->hasRole($this->testUserRole))->toBeTrue();
    }
})->with([false, true]);

it('does nothing when syncing an unsaved role', function () {
    $role = new Role(['name' => 'unsaved-role']);

    expect($role->syncModels($this->testUser))->toBe($role);
    expect($this->testUser->fresh()->roles)->toBeEmpty();
});

it('uses config default_model when resolving IDs', function () {
    config()->set('permission.models.default_model', User::class);

    $user1 = User::create(['email' => 'user1@test.com']);

    $this->testUserRole->syncModels([$user1->getKey()]);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeTrue();
});
