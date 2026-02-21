<?php

use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Tests\TestSupport\TestModels\User;

// --- syncModels ---

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

// --- attachModels ---

it('can attach models to a role', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    $this->testUserRole->attachModels([$user1, $user2]);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeTrue();
    expect($user2->fresh()->hasRole($this->testUserRole))->toBeTrue();
});

it('can attach a single model instance', function () {
    $user1 = User::create(['email' => 'user1@test.com']);

    $this->testUserRole->attachModels($user1);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeTrue();
});

it('can attach models using IDs', function () {
    $user1 = User::create(['email' => 'user1@test.com']);

    $this->testUserRole->attachModels($user1->getKey());

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeTrue();
});

it('does not attach duplicate models', function () {
    $user1 = User::create(['email' => 'user1@test.com']);

    $this->testUserRole->attachModels([$user1, $user1]);

    $count = DB::table(config('permission.table_names.model_has_roles'))
        ->where(app(PermissionRegistrar::class)->pivotRole, $this->testUserRole->getKey())
        ->count();

    expect($count)->toBe(1);
});

it('does not attach already attached models', function () {
    $user1 = User::create(['email' => 'user1@test.com']);

    $this->testUserRole->attachModels($user1);
    $this->testUserRole->attachModels($user1);

    $count = DB::table(config('permission.table_names.model_has_roles'))
        ->where(app(PermissionRegistrar::class)->pivotRole, $this->testUserRole->getKey())
        ->count();

    expect($count)->toBe(1);
});

it('can attach additional models without removing existing ones', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    $this->testUserRole->attachModels($user1);
    $this->testUserRole->attachModels($user2);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeTrue();
    expect($user2->fresh()->hasRole($this->testUserRole))->toBeTrue();
});

// --- detachModels ---

it('can detach models from a role', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    $user1->assignRole($this->testUserRole);
    $user2->assignRole($this->testUserRole);

    $this->testUserRole->detachModels([$user1]);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeFalse();
    expect($user2->fresh()->hasRole($this->testUserRole))->toBeTrue();
});

it('can detach a single model instance', function () {
    $user1 = User::create(['email' => 'user1@test.com']);

    $user1->assignRole($this->testUserRole);

    $this->testUserRole->detachModels($user1);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeFalse();
});

it('can detach models using IDs', function () {
    $user1 = User::create(['email' => 'user1@test.com']);

    $user1->assignRole($this->testUserRole);

    $this->testUserRole->detachModels($user1->getKey());

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeFalse();
});

it('does nothing when detaching models that are not attached', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    $user1->assignRole($this->testUserRole);

    $this->testUserRole->detachModels($user2);

    expect($user1->fresh()->hasRole($this->testUserRole))->toBeTrue();
});
