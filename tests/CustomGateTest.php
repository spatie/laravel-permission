<?php

use Illuminate\Contracts\Auth\Access\Gate;
use Spatie\Permission\Tests\CustomGateTestCase;

uses(CustomGateTestCase::class);

it('doesnt register the method for checking permissions on the gate', function () {
    $this->testUser->givePermissionTo('edit-articles');

    expect(app(Gate::class)->abilities())->toBeEmpty();
    expect($this->testUser->can('edit-articles'))->toBeFalse();
});

it('can authorize using custom method for checking permissions', function () {
    app(Gate::class)->define('edit-articles', function () {
        return true;
    });

    expect(app(Gate::class)->abilities())->toHaveKey('edit-articles');
    expect($this->testUser->can('edit-articles'))->toBeTrue();
});
