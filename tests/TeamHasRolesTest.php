<?php

namespace Spatie\Permission\Tests;

use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Tests\TestModels\User;

include "HasRolesTest.php";

trait SetupTeamHasRolesTest {
    protected function getEnvironmentSetUp($app)
    {
        $this->hasTeams = true;

        parent::getEnvironmentSetUp($app);
    }
}

uses(SetupTeamHasRolesTest::class);

it('deletes pivot table entries when deleting models that has teams', function () {
    $user1 = User::create(['email' => 'user2@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    setPermissionsTeamId(1);
    $user1->assignRole('testRole');
    $user1->givePermissionTo('edit-articles');
    $user2->assignRole('testRole');
    $user2->givePermissionTo('edit-articles');
    setPermissionsTeamId(2);
    $user1->givePermissionTo('edit-news');

    $this->assertDatabaseHas('model_has_permissions', [config('permission.column_names.model_morph_key') => $user1->id]);
    $this->assertDatabaseHas('model_has_roles', [config('permission.column_names.model_morph_key') => $user1->id]);

    $user1->delete();

    setPermissionsTeamId(1);
    $this->assertDatabaseMissing('model_has_permissions', [config('permission.column_names.model_morph_key') => $user1->id]);
    $this->assertDatabaseMissing('model_has_roles', [config('permission.column_names.model_morph_key') => $user1->id]);
    $this->assertDatabaseHas('model_has_permissions', [config('permission.column_names.model_morph_key') => $user2->id]);
    $this->assertDatabaseHas('model_has_roles', [config('permission.column_names.model_morph_key') => $user2->id]);
});

it('can assign same and different roles on same user different teams', function () {
    app(Role::class)->create(['name' => 'testRole3']); //team_test_id = 1 by main class
    app(Role::class)->create(['name' => 'testRole3', 'team_test_id' => 2]);
    app(Role::class)->create(['name' => 'testRole4', 'team_test_id' => null]); //global role

    $testRole3Team1 = app(Role::class)->where(['name' => 'testRole3', 'team_test_id' => 1])->first();
    $testRole3Team2 = app(Role::class)->where(['name' => 'testRole3', 'team_test_id' => 2])->first();
    $testRole4NoTeam = app(Role::class)->where(['name' => 'testRole4', 'team_test_id' => null])->first();
    expect($testRole3Team1)->not->toBeNull()
        ->and($testRole4NoTeam)->not->toBeNull();

    setPermissionsTeamId(1);
    $this->testUser->load('roles');
    $this->testUser->assignRole('testRole', 'testRole2');

    setPermissionsTeamId(2);
    $this->testUser->load('roles');
    $this->testUser->assignRole('testRole', 'testRole3');

    setPermissionsTeamId(1);
    $this->testUser->load('roles');

    expect($this->testUser->getRoleNames()->sort()->values())->toEqual(collect(['testRole', 'testRole2']))
        ->and($this->testUser->hasExactRoles(['testRole', 'testRole2']))->toBeTrue();

    $this->testUser->assignRole('testRole3', 'testRole4');
    expect($this->testUser->hasExactRoles(['testRole', 'testRole2', 'testRole3', 'testRole4']))->toBeTrue()
        ->and($this->testUser->hasRole($testRole3Team1))->toBeTrue() //testRole3 team=1
        ->and($this->testUser->hasRole($testRole4NoTeam))->toBeTrue(); // global role team=null

    setPermissionsTeamId(2);
    $this->testUser->load('roles');

    expect($this->testUser->getRoleNames()->sort()->values())->toEqual(collect(['testRole', 'testRole3']))
        ->and($this->testUser->hasExactRoles(['testRole', 'testRole3']))->toBeTrue()
        ->and($this->testUser->hasRole($testRole3Team2))->toBeTrue(); //testRole3 team=2
    $this->testUser->assignRole('testRole4');
    expect($this->testUser->hasExactRoles(['testRole', 'testRole3', 'testRole4']))->toBeTrue()
        ->and($this->testUser->hasRole($testRole4NoTeam))->toBeTrue(); // global role team=null
});

it('can sync or remove roles without detach on different teams', function () {
    app(Role::class)->create(['name' => 'testRole3', 'team_test_id' => 2]);

    setPermissionsTeamId(1);
    $this->testUser->load('roles');
    $this->testUser->syncRoles('testRole', 'testRole2');

    setPermissionsTeamId(2);
    $this->testUser->load('roles');
    $this->testUser->syncRoles('testRole', 'testRole3');

    setPermissionsTeamId(1);
    $this->testUser->load('roles');

    expect($this->testUser->getRoleNames()->sort()->values())->toEqual(collect(['testRole', 'testRole2']));

    $this->testUser->removeRole('testRole');
    expect($this->testUser->getRoleNames()->sort()->values())->toEqual(collect(['testRole2']));

    setPermissionsTeamId(2);
    $this->testUser->load('roles');

    expect($this->testUser->getRoleNames()->sort()->values())->toEqual(collect(['testRole', 'testRole3']));
});

it('can scope users on different teams', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    setPermissionsTeamId(2);
    $user1->assignRole($this->testUserRole);
    $user2->assignRole('testRole2');

    setPermissionsTeamId(1);
    $user1->assignRole('testRole');

    setPermissionsTeamId(2);
    $scopedUsers1Team1 = User::role($this->testUserRole)->get();
    $scopedUsers2Team1 = User::role(['testRole', 'testRole2'])->get();

    expect($scopedUsers1Team1->count())->toEqual(1)
        ->and($scopedUsers2Team1->count())->toEqual(2);

    setPermissionsTeamId(1);
    $scopedUsers1Team2 = User::role($this->testUserRole)->get();
    $scopedUsers2Team2 = User::role('testRole2')->get();

    expect($scopedUsers1Team2->count())->toEqual(1)
        ->and($scopedUsers2Team2->count())->toEqual(0);
});
