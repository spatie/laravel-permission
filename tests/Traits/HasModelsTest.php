<?php

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Contracts\Role;
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

it('will remove all models when an empty array is passed to syncModels', function () {
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

    $count = DB::table(config('permission.table_names.model_has_roles'))
        ->where(app(\Spatie\Permission\PermissionRegistrar::class)->pivotRole, $this->testUserRole->getKey())
        ->count();

    expect($count)->toBe(1);
    expect($user1->fresh()->hasRole($this->testUserRole))->toBeTrue();
});

it('can sync models using a single model instance', function () {
    $user1 = User::create(['email' => 'user1@test.com']);

    $this->testUserRole->syncModels($user1);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeTrue();
});

it('does not run unnecessary queries when syncing models', function () {
    $user1 = User::create(['email' => 'user1@test.com']);

    DB::enableQueryLog();

    $this->testUserRole->syncModels([$user1]);

    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    // Should be: 1 select for morph types + 1 insert (no detach when table is empty)
    expect(count($queries))->toBeLessThanOrEqual(3);
});
