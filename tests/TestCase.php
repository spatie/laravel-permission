<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Contracts\Role;
use Illuminate\Database\Schema\Blueprint;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Contracts\Permission;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Permission\PermissionServiceProvider;

abstract class TestCase extends Orchestra
{
    /** @var \Spatie\Permission\Test\User */
    protected $testUser;

    /** @var \Spatie\Permission\Models\Role */
    protected $testUserRole;

    /** @var \Spatie\Permission\Models\Permission */
    protected $testUserPermission;

    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        $this->testUser = User::first();
        $this->testUserRole = app(Role::class)->find(1);
        $this->testUserPermission = app(Permission::class)->find(1);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            PermissionServiceProvider::class,
        ];
    }

    /**
     * Set up the environment.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('view.paths', [__DIR__.'/resources/views']);
    }

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->softDeletes();
        });

        include_once __DIR__.'/../database/migrations/create_permission_tables.php.stub';

        (new \CreatePermissionTables())->up();

        User::create(['email' => 'test@user.com']);
        $app[Role::class]->create(['name' => 'testRole']);
        $app[Role::class]->create(['name' => 'testRole2']);
        $app[Permission::class]->create(['name' => 'edit-articles']);
        $app[Permission::class]->create(['name' => 'edit-news']);
        $app[Permission::class]->create(['name' => 'edit-blog']);
    }

    /**
     * Reload the permissions.
     */
    protected function reloadPermissions()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Refresh the testUser.
     */
    public function refreshTestUser()
    {
        $this->testUser = $this->testUser->fresh();
    }

    /**
     * Refresh the testUserPermission.
     */
    public function refreshTestUserPermission()
    {
        $this->testUserPermission = $this->testUserPermission->fresh();
    }
}
