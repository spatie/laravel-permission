<?php

use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Tests\TeamTestCase;
use Spatie\Permission\Tests\TestModels\User;

uses(TeamTestCase::class);

it('can assign role to user with team id', function () {
    $user = User::first();

    Artisan::call('permission:assign-role', [
        'name' => 'testRole',
        'userId' => $user->id,
        'guard' => 'web',
        'userModelNamespace' => User::class,
        '--team-id' => 1,
    ]);

    $output = Artisan::output();

    expect($output)->toContain('Role `testRole` assigned to user ID '.$user->id.' successfully.');

    setPermissionsTeamId(1);
    $user->unsetRelation('roles');
    expect($user->hasRole('testRole'))->toBeTrue();
});

it('can assign role to user on different teams', function () {
    $user = User::first();

    Artisan::call('permission:assign-role', [
        'name' => 'testRole',
        'userId' => $user->id,
        'guard' => 'web',
        'userModelNamespace' => User::class,
        '--team-id' => 1,
    ]);

    Artisan::call('permission:assign-role', [
        'name' => 'testRole2',
        'userId' => $user->id,
        'guard' => 'web',
        'userModelNamespace' => User::class,
        '--team-id' => 2,
    ]);

    setPermissionsTeamId(1);
    $user->unsetRelation('roles');
    expect($user->hasRole('testRole'))->toBeTrue();
    expect($user->hasRole('testRole2'))->toBeFalse();

    setPermissionsTeamId(2);
    $user->unsetRelation('roles');
    expect($user->hasRole('testRole2'))->toBeTrue();
    expect($user->hasRole('testRole'))->toBeFalse();
});

it('restores previous team id after assigning role', function () {
    $user = User::first();

    setPermissionsTeamId(5);

    Artisan::call('permission:assign-role', [
        'name' => 'testRole',
        'userId' => $user->id,
        'guard' => 'web',
        'userModelNamespace' => User::class,
        '--team-id' => 1,
    ]);

    expect(getPermissionsTeamId())->toEqual(5);
});
