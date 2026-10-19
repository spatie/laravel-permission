<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Events\RoleAttachedEvent;
use Spatie\Permission\Events\RoleDetachedEvent;
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

it('does not detach models when syncing an unsaved model', function () {
    $user = User::create(['email' => 'user@test.com']);
    $this->testUserRole->syncModels($user);

    $unsavedUser = new User(['email' => 'unsaved-user@test.com']);

    expect(fn () => $this->testUserRole->syncModels($unsavedUser))->toThrow(QueryException::class);

    expect($user->fresh()->hasRole($this->testUserRole))->toBeTrue();
});

it('rolls back earlier model types and all teams when a later attachment fails', function (bool $teams) {
    if ($teams) {
        $this->setUpTeams();
    }

    $role = $this->testUserRole;
    $role->assignToModels([$this->testUser, $this->testAdmin]);

    if ($teams) {
        setPermissionsTeamId(2);
        $role->assignToModels(User::create(['email' => 'other-team@test.com']));
        setPermissionsTeamId(1);
    }

    $before = DB::table(Config::modelHasRolesTable())->get()->all();
    $role->load('users');
    $loadedUsers = $role->users;
    $replacement = User::create(['email' => 'replacement@test.com']);

    expect(fn () => $role->syncModels([$replacement, new Admin]))->toThrow(QueryException::class);

    expect(DB::table(Config::modelHasRolesTable())->get()->all())->toEqual($before)
        ->and($role->users)->toBe($loadedUsers)
        ->and($replacement->fresh()->hasRole($role))->toBeFalse();
})->with(['without teams' => false, 'with teams' => true]);

it('preserves assignments when the pivot unique constraint rejects replacement ids', function () {
    $role = $this->testUserRole;
    $role->assignToModels($this->testUser);
    $replacement = User::create(['email' => 'replacement@test.com']);

    expect(fn () => $role->syncModels([$replacement->getKey(), (string) $replacement->getKey()]))
        ->toThrow(QueryException::class);

    expect($this->testUser->fresh()->hasRole($role))->toBeTrue()
        ->and($replacement->fresh()->hasRole($role))->toBeFalse();
});

it('can recover from a failed sync inside a transaction without losing unrelated writes', function () {
    $role = $this->testUserRole;
    $role->assignToModels($this->testUser);

    $replacement = DB::transaction(function () use ($role) {
        $replacement = User::create(['email' => 'replacement@test.com']);

        expect(fn () => $role->syncModels([$replacement, new Admin]))->toThrow(QueryException::class);
        expect($this->testUser->fresh()->hasRole($role))->toBeTrue()
            ->and($replacement->fresh()->hasRole($role))->toBeFalse();

        expect($role->syncModels($replacement))->toBe($role);

        return $replacement;
    });

    expect($replacement->fresh()->hasRole($role))->toBeTrue()
        ->and($this->testUser->fresh()->hasRole($role))->toBeFalse();
});

it('allows an outer transaction to roll back a successful sync', function () {
    $role = $this->testUserRole;
    $role->assignToModels($this->testUser);

    expect(fn () => DB::transaction(function () use ($role) {
        $replacement = User::create(['email' => 'replacement@test.com']);
        $role->syncModels($replacement);

        expect($replacement->fresh()->hasRole($role))->toBeTrue();

        throw new RuntimeException('Roll back the outer transaction');
    }))->toThrow(RuntimeException::class, 'Roll back the outer transaction');

    expect($this->testUser->fresh()->hasRole($role))->toBeTrue()
        ->and(User::where('email', 'replacement@test.com')->exists())->toBeFalse();
});

it('uses the role connection rather than the default connection for rollback', function () {
    $role = $this->testUserRole;
    $role->assignToModels($this->testUser);
    $replacement = User::create(['email' => 'replacement@test.com']);

    config()->set('database.connections.other', config('database.connections.sqlite'));
    DB::setDefaultConnection('other');

    expect(fn () => $role->syncModels([$replacement, new Admin]))->toThrow(QueryException::class);

    expect($this->testUser->fresh()->hasRole($role))->toBeTrue()
        ->and($replacement->fresh()->hasRole($role))->toBeFalse();
});

it('preserves other roles when an unsaved role cannot be synced', function () {
    $this->testUserRole->assignToModels($this->testUser);
    $role = new Role(['name' => 'unsaved-role']);

    expect(fn () => $role->syncModels($this->testUser))->toThrow(QueryException::class);

    expect($role->exists)->toBeFalse()
        ->and($this->testUser->fresh()->roles->modelKeys())->toBe([$this->testUserRole->getKey()]);
});

it('replaces all model types and clears loaded users without emitting role events', function () {
    config()->set('permission.events_enabled', true);
    Event::fake([RoleAttachedEvent::class, RoleDetachedEvent::class]);

    $role = $this->testUserRole;
    $role->assignToModels([$this->testUser, $this->testAdmin]);
    $role->load('users');
    $replacementUser = User::create(['email' => 'replacement@test.com']);
    $replacementAdmin = Admin::create(['email' => 'replacement-admin@test.com']);

    expect($role->syncModels([$replacementUser, $replacementAdmin]))->toBe($role);

    expect($this->testUser->fresh()->hasRole($role))->toBeFalse()
        ->and($role->relationLoaded('users'))->toBeFalse()
        ->and($role->users()->get()->modelKeys())->toBe([$replacementUser->getKey()]);

    $this->assertDatabaseHas(Config::modelHasRolesTable(), [
        app(PermissionRegistrar::class)->pivotRole => $role->getKey(),
        'model_type' => $replacementAdmin->getMorphClass(),
        Config::morphKey() => $replacementAdmin->getKey(),
    ]);
    $this->assertDatabaseMissing(Config::modelHasRolesTable(), [
        app(PermissionRegistrar::class)->pivotRole => $role->getKey(),
        'model_type' => $this->testAdmin->getMorphClass(),
        Config::morphKey() => $this->testAdmin->getKey(),
    ]);

    expect(fn () => $role->syncModels(new User))->toThrow(QueryException::class);
    Event::assertNotDispatched(RoleAttachedEvent::class);
    Event::assertNotDispatched(RoleDetachedEvent::class);
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

it('uses config default_model when resolving IDs', function () {
    config()->set('permission.models.default_model', User::class);

    $user1 = User::create(['email' => 'user1@test.com']);

    $this->testUserRole->syncModels([$user1->getKey()]);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeTrue();
});
