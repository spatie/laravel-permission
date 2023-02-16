<?php

namespace Spatie\Permission\Tests;

use Illuminate\Contracts\Auth\Access\Gate;

trait SetupCustomGateTest {
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('permission.register_permission_check_method', false);
    }
}

uses(SetupCustomGateTest::class);

it('doesnt register the method for checking permissions on the gate', function () {
    $this->testUser->givePermissionTo('edit-articles');

    expect(app(Gate::class)->abilities())->toBeEmpty()
        ->and($this->testUser->can('edit-articles'))->toBeFalse();
});

it('can authorize using custom method for checking permissions', function () {
    app(Gate::class)->define('edit-articles', function () {
        return true;
    });

    expect(app(Gate::class)->abilities())->toHaveKey('edit-articles');
    expect($this->testUser->can('edit-articles'))->toBeTrue();
});
