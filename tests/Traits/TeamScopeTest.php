<?php

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Exceptions\TeamModelNotConfigured;
use Spatie\Permission\Exceptions\TeamsNotEnabled;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Tests\TestSupport\TestModels\Team;
use Spatie\Permission\Tests\TestSupport\TestModels\User;

beforeEach(function () {
    $this->setUpTeams();

    Schema::create('teams', function (Blueprint $table) {
        $table->increments('id');
        $table->string('name');
    });

    app(PermissionRegistrar::class)->setTeamClass(Team::class);

    User::query()->delete();
});

afterEach(function () {
    Schema::dropIfExists('teams');
});

it('throws an exception when team scopes are queried while teams are not enabled', function () {
    config()->set('permission.teams', false);
    app(PermissionRegistrar::class)->teams = false;

    expect(fn () => User::team(1)->get())->toThrow(TeamsNotEnabled::class);
    expect(fn () => User::withoutTeam(1)->get())->toThrow(TeamsNotEnabled::class);
});

it('returns an empty teams relation when teams are not enabled so model introspection does not break', function () {
    config()->set('permission.teams', false);
    app(PermissionRegistrar::class)->teams = false;

    $relation = $this->testUser->teams();

    expect($relation)->toBeInstanceOf(BelongsToMany::class);
    expect($relation->get())->toHaveCount(0);
});

it('throws an exception when team model is not configured', function () {
    app(PermissionRegistrar::class)->setTeamClass(null);
    config()->set('permission.models.team', null);

    expect(fn () => User::team(1)->get())->toThrow(TeamModelNotConfigured::class);
    expect(fn () => User::withoutTeam(1)->get())->toThrow(TeamModelNotConfigured::class);
    expect(fn () => $this->testUser->teams()->get())->toThrow(TeamModelNotConfigured::class);
});

it('returns the teams a user belongs to via the teams relation', function () {
    Team::create(['id' => 1, 'name' => 'Team One']);
    Team::create(['id' => 2, 'name' => 'Team Two']);

    $user = User::create(['email' => 'user1@test.com']);

    setPermissionsTeamId(1);
    $user->assignRole('testRole');

    setPermissionsTeamId(2);
    $user->assignRole('testRole');

    $teams = $user->teams()->get();

    expect($teams)->toHaveCount(2);
    expect($teams->pluck('id')->all())->toEqualCanonicalizing([1, 2]);
});

it('can scope users by team using an id', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user3 = User::create(['email' => 'user3@test.com']);

    setPermissionsTeamId(1);
    $user1->assignRole('testRole');
    $user2->assignRole('testRole');

    setPermissionsTeamId(2);
    $user3->assignRole('testRole');

    expect(User::team(1)->get())->toHaveCount(2);
    expect(User::team(2)->get())->toHaveCount(1);
});

it('can scope users by team using an array of ids', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    setPermissionsTeamId(1);
    $user1->assignRole('testRole');

    setPermissionsTeamId(2);
    $user2->assignRole('testRole');

    expect(User::team([1, 2])->get())->toHaveCount(2);
    expect(User::team([1])->get())->toHaveCount(1);
    expect(User::team([2])->get())->toHaveCount(1);
});

it('can scope users by team using a collection', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    setPermissionsTeamId(1);
    $user1->assignRole('testRole');

    setPermissionsTeamId(2);
    $user2->assignRole('testRole');

    expect(User::team(collect([1, 2]))->get())->toHaveCount(2);
    expect(User::team(collect([1]))->get())->toHaveCount(1);
});

it('can scope users by team using a model instance', function () {
    $teamOne = Team::create(['id' => 1, 'name' => 'Team One']);
    $teamTwo = Team::create(['id' => 2, 'name' => 'Team Two']);

    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    setPermissionsTeamId(1);
    $user1->assignRole('testRole');

    setPermissionsTeamId(2);
    $user2->assignRole('testRole');

    expect(User::team($teamOne)->get())->toHaveCount(1);
    expect(User::team([$teamOne, $teamTwo])->get())->toHaveCount(2);
});

it('returns unique users when user has multiple roles in the same team', function () {
    $user1 = User::create(['email' => 'user1@test.com']);

    setPermissionsTeamId(1);
    $user1->assignRole('testRole');
    $user1->assignRole('testRole2');

    expect(User::team(1)->get())->toHaveCount(1);
});

it('can scope users without a team using an id', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user3 = User::create(['email' => 'user3@test.com']);

    setPermissionsTeamId(1);
    $user1->assignRole('testRole');
    $user2->assignRole('testRole');

    setPermissionsTeamId(2);
    $user3->assignRole('testRole');

    expect(User::withoutTeam(1)->get())->toHaveCount(1);
    expect(User::withoutTeam(2)->get())->toHaveCount(2);
});

it('can scope users without a team using an array of ids', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user3 = User::create(['email' => 'user3@test.com']);

    setPermissionsTeamId(1);
    $user1->assignRole('testRole');

    setPermissionsTeamId(2);
    $user2->assignRole('testRole');

    expect(User::withoutTeam([1, 2])->get())->toHaveCount(1);
    expect(User::withoutTeam([1])->get())->toHaveCount(2);
});

it('can scope users without a team using a collection', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user3 = User::create(['email' => 'user3@test.com']);

    setPermissionsTeamId(1);
    $user1->assignRole('testRole');

    setPermissionsTeamId(2);
    $user2->assignRole('testRole');

    expect(User::withoutTeam(collect([1, 2]))->get())->toHaveCount(1);
    expect(User::withoutTeam(collect([1]))->get())->toHaveCount(2);
});

it('does not mix up users from different teams', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    setPermissionsTeamId(1);
    $user1->assignRole('testRole');
    $user1->assignRole('testRole2');

    setPermissionsTeamId(2);
    $user2->assignRole('testRole');

    $inTeam1 = User::team(1)->get();
    $inTeam2 = User::team(2)->get();

    expect($inTeam1)->toHaveCount(1);
    expect($inTeam1->first()->id)->toEqual($user1->id);

    expect($inTeam2)->toHaveCount(1);
    expect($inTeam2->first()->id)->toEqual($user2->id);
});
