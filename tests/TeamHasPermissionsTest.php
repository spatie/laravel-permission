<?php

namespace Spatie\Permission\Tests;

use Spatie\Permission\Tests\TestModels\User;

include "HasPermissionsTest.php";

trait SetupTeamHasPermissionsTest {
    protected function getEnvironmentSetUp($app)
    {
        $this->hasTeams = true;

        parent::getEnvironmentSetUp($app);
    }
}

uses(SetupTeamHasPermissionsTest::class);

it('can assign same and different permission on same user on different teams', function () {
    setPermissionsTeamId(1);
    $this->testUser->load('permissions');
    $this->testUser->givePermissionTo('edit-articles', 'edit-news');

    setPermissionsTeamId(2);
    $this->testUser->load('permissions');
    $this->testUser->givePermissionTo('edit-articles', 'edit-blog');

    setPermissionsTeamId(1);
    $this->testUser->load('permissions');
    expect(collect(['edit-articles', 'edit-news']))->toEqual($this->testUser->getPermissionNames()->sort()->values())
        ->and($this->testUser->hasAllDirectPermissions(['edit-articles', 'edit-news']))->toBeTrue()
        ->and($this->testUser->hasAllDirectPermissions(['edit-articles', 'edit-blog']))->toBeFalse();

    setPermissionsTeamId(2);
    $this->testUser->load('permissions');
    expect($this->testUser->getPermissionNames()->sort()->values())->toEqual(collect(['edit-articles', 'edit-blog']))
        ->and($this->testUser->hasAllDirectPermissions(['edit-articles', 'edit-blog']))->toBeTrue()
        ->and($this->testUser->hasAllDirectPermissions(['edit-articles', 'edit-news']))->toBeFalse();
});

it('can list all the coupled permissions both directly and via roles on same user on different teams', function () {
    $this->testUserRole->givePermissionTo('edit-articles');

    setPermissionsTeamId(1);
    $this->testUser->load('permissions');
    $this->testUser->assignRole('testRole');
    $this->testUser->givePermissionTo('edit-news');

    setPermissionsTeamId(2);
    $this->testUser->load('permissions');
    $this->testUser->assignRole('testRole');
    $this->testUser->givePermissionTo('edit-blog');

    setPermissionsTeamId(1);
    $this->testUser->load('roles');
    $this->testUser->load('permissions');

    expect($this->testUser->getAllPermissions()->pluck('name')->sort()->values())
        ->toEqual(collect(['edit-articles', 'edit-news']));

    setPermissionsTeamId(2);
    $this->testUser->load('roles');
    $this->testUser->load('permissions');

    expect($this->testUser->getAllPermissions()->pluck('name')->sort()->values())
        ->toEqual(collect(['edit-articles', 'edit-blog']));
});

it('can sync or remove permission without detach on different teams', function () {
    setPermissionsTeamId(1);
    $this->testUser->load('permissions');
    $this->testUser->syncPermissions('edit-articles', 'edit-news');

    setPermissionsTeamId(2);
    $this->testUser->load('permissions');
    $this->testUser->syncPermissions('edit-articles', 'edit-blog');

    setPermissionsTeamId(1);
    $this->testUser->load('permissions');

    expect(collect(['edit-articles', 'edit-news']))->toEqual($this->testUser->getPermissionNames()->sort()->values());

    $this->testUser->revokePermissionTo('edit-articles');
    expect($this->testUser->getPermissionNames()->sort()->values())->toEqual(collect(['edit-news']));

    setPermissionsTeamId(2);
    $this->testUser->load('permissions');
    expect($this->testUser->getPermissionNames()->sort()->values())->toEqual(collect(['edit-articles', 'edit-blog']));
});

it('can scope users on different teams', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    setPermissionsTeamId(2);
    $user1->givePermissionTo(['edit-articles', 'edit-news']);
    $this->testUserRole->givePermissionTo('edit-articles');
    $user2->assignRole('testRole');

    setPermissionsTeamId(1);
    $user1->givePermissionTo(['edit-articles']);

    setPermissionsTeamId(2);
    $scopedUsers1Team2 = User::permission(['edit-articles', 'edit-news'])->get();
    $scopedUsers2Team2 = User::permission('edit-news')->get();

    expect($scopedUsers1Team2->count())->toEqual(2)
        ->and($scopedUsers2Team2->count())->toEqual(1);

    setPermissionsTeamId(1);
    $scopedUsers1Team1 = User::permission(['edit-articles', 'edit-news'])->get();
    $scopedUsers2Team1 = User::permission('edit-news')->get();

    expect($scopedUsers1Team1->count())->toEqual(1)
        ->and($scopedUsers2Team1->count())->toEqual(0);
});
