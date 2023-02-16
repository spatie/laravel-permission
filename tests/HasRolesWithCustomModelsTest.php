<?php

namespace Spatie\Permission\Tests;

use Spatie\Permission\Tests\TestModels\Role;

include "HasRolesTest.php";

trait SetupHasRolesWithCustomModelsTest {
    protected function getEnvironmentSetUp($app)
    {
        $this->useCustomModels = true;

        parent::getEnvironmentSetUp($app);
    }
}

uses(SetupHasRolesWithCustomModelsTest::class);

it('can_use_custom_model_role', function () {
    expect(Role::class)->toBe(get_class($this->testUserRole));
});
