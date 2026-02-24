<?php

use Spatie\Permission\Exceptions\TeamModelNotConfigured;
use Spatie\Permission\Exceptions\TeamsNotEnabled;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Tests\TestSupport\TestModels\User;

beforeEach(function () {
    $this->setUpTeams();
    // Use User::class as a valid class reference for the team model config.
    // scopeTeam only needs a non-null class for instanceof checks;
    // we always pass plain integer IDs in these tests, so the actual model is irrelevant.
    app('config')->set('permission.models.team', User::class);
});

it('throws an exception when teams are not enabled', function () {
    app('config')->set('permission.teams', false);
    app(PermissionRegistrar::class)->teams = false;

    expect(fn () => User::team(1)->get())->toThrow(TeamsNotEnabled::class);
    expect(fn () => User::withoutTeam(1)->get())->toThrow(TeamsNotEnabled::class);
});

it('throws an exception when team model is not configured', function () {
    app('config')->set('permission.models.team', null);

    expect(fn () => User::team(1)->get())->toThrow(TeamModelNotConfigured::class);
    expect(fn () => User::withoutTeam(1)->get())->toThrow(TeamModelNotConfigured::class);
    expect(fn () => $this->testUser->teams()->get())->toThrow(TeamModelNotConfigured::class);
});

it('can scope users by team using an id', function () {
    User::all()->each(fn ($u) => $u->delete());
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user3 = User::create(['email' => 'user3@test.com']);

    setPermissionsTeamId(1);
    $user1->assignRole('testRole');
    $user2->assignRole('testRole');

    setPermissionsTeamId(2);
    $user3->assignRole('testRole');

    expect(User::team(1)->get()->count())->toEqual(2);
    expect(User::team(2)->get()->count())->toEqual(1);
});

it('can scope users by team using an array of ids', function () {
    User::all()->each(fn ($u) => $u->delete());
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    setPermissionsTeamId(1);
    $user1->assignRole('testRole');

    setPermissionsTeamId(2);
    $user2->assignRole('testRole');

    expect(User::team([1, 2])->get()->count())->toEqual(2);
    expect(User::team([1])->get()->count())->toEqual(1);
    expect(User::team([2])->get()->count())->toEqual(1);
});

it('can scope users by team using a collection', function () {
    User::all()->each(fn ($u) => $u->delete());
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    setPermissionsTeamId(1);
    $user1->assignRole('testRole');

    setPermissionsTeamId(2);
    $user2->assignRole('testRole');

    expect(User::team(collect([1, 2]))->get()->count())->toEqual(2);
    expect(User::team(collect([1]))->get()->count())->toEqual(1);
});

it('returns unique users when user has multiple roles in the same team', function () {
    User::all()->each(fn ($u) => $u->delete());
    $user1 = User::create(['email' => 'user1@test.com']);

    setPermissionsTeamId(1);
    $user1->assignRole('testRole');
    $user1->assignRole('testRole2');

    expect(User::team(1)->get()->count())->toEqual(1);
});

it('can scope users without a team using an id', function () {
    User::all()->each(fn ($u) => $u->delete());
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user3 = User::create(['email' => 'user3@test.com']);

    setPermissionsTeamId(1);
    $user1->assignRole('testRole');
    $user2->assignRole('testRole');

    setPermissionsTeamId(2);
    $user3->assignRole('testRole');

    // user3 has no role in team 1
    expect(User::withoutTeam(1)->get()->count())->toEqual(1);
    // user1 and user2 have no role in team 2
    expect(User::withoutTeam(2)->get()->count())->toEqual(2);
});

it('can scope users without a team using an array of ids', function () {
    User::all()->each(fn ($u) => $u->delete());
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user3 = User::create(['email' => 'user3@test.com']);

    setPermissionsTeamId(1);
    $user1->assignRole('testRole');

    setPermissionsTeamId(2);
    $user2->assignRole('testRole');

    // user3 has no role in team 1 or 2
    expect(User::withoutTeam([1, 2])->get()->count())->toEqual(1);
    // user2 and user3 have no role in team 1
    expect(User::withoutTeam([1])->get()->count())->toEqual(2);
});

it('can scope users without a team using a collection', function () {
    User::all()->each(fn ($u) => $u->delete());
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user3 = User::create(['email' => 'user3@test.com']);

    setPermissionsTeamId(1);
    $user1->assignRole('testRole');

    setPermissionsTeamId(2);
    $user2->assignRole('testRole');

    // user3 has no role in team 1 or 2
    expect(User::withoutTeam(collect([1, 2]))->get()->count())->toEqual(1);
    // user2 and user3 have no role in team 1
    expect(User::withoutTeam(collect([1]))->get()->count())->toEqual(2);
});

it('does not mix up users from different teams', function () {
    User::all()->each(fn ($u) => $u->delete());
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    setPermissionsTeamId(1);
    $user1->assignRole('testRole');
    $user1->assignRole('testRole2');

    setPermissionsTeamId(2);
    $user2->assignRole('testRole');

    $inTeam1 = User::team(1)->get();
    $inTeam2 = User::team(2)->get();

    expect($inTeam1->count())->toEqual(1);
    expect($inTeam1->first()->id)->toEqual($user1->id);

    expect($inTeam2->count())->toEqual(1);
    expect($inTeam2->first()->id)->toEqual($user2->id);
});
