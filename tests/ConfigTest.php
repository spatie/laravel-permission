<?php

namespace Spatie\Permission\Test;


use Spatie\Permission\Models\Permission;

class ConfigTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['permission.models.permission'] = PermissionStub::class;
        $app['permission.table_names.permission'] = 'another_permission_table';
    }

    /** @test */
    public function a_command_will_use_the_permission_model_setting()
    {
        Artisan::call('permission:create-permission', ['name' => 'new-permission']);

        $this->assertCount(1, PermissionStub::where('name', 'new-permission')->get());
    }
}


class PermissionStub extends Permission
{
    protected $table = 'another_permission_table';
}