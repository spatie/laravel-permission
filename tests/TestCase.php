<?php

namespace Spatie\Permission\Test;

use Monolog\Handler\TestHandler;
use Spatie\Permission\Contracts\Role;
use Illuminate\Database\Schema\Blueprint;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Contracts\Permission;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Permission\PermissionServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @var \Spatie\Permission\Test\User
     */
    protected $testUser;

    /**
     * @var \Spatie\Permission\Models\Role
     */
    protected $testRole;

    /**
     * @var \Spatie\Permission\Models\Permission
     */
    protected $testPermission;

    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        $this->reloadPermissions();

        $this->testUser = User::first();
        $this->testRole = app(Role::class)->first();
        $this->testPermission = app(Permission::class)->find(1);

        $this->clearLogTestHandler();
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

        $app['log']->getMonolog()->pushHandler(new TestHandler());
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
        });

        include_once __DIR__.'/../resources/migrations/create_permission_tables.php.stub';

        (new \CreatePermissionTables())->up();

        User::create(['email' => 'test@user.com']);
        $app[Role::class]->create(['name' => 'testRole']);
        $app[Role::class]->create(['name' => 'testRole2']);
        $app[Permission::class]->create(['name' => 'edit-articles']);
        $app[Permission::class]->create(['name' => 'edit-news']);
    }

    /**
     * Reload the permissions.
     *
     * @return bool
     */
    protected function reloadPermissions()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return app(PermissionRegistrar::class)->registerPermissions();
    }

    /**
     * Refresh the testuser.
     */
    public function refreshTestUser()
    {
        $this->testUser = $this->testUser->fresh();
    }

    protected function clearLogTestHandler()
    {
        collect($this->app['log']->getMonolog()->getHandlers())->filter(function ($handler) {
            return $handler instanceof TestHandler;
        })->first(function (TestHandler $handler) {
            $handler->clear();
        });
    }

    protected function assertNotLogged($message, $level)
    {
        $this->assertFalse($this->hasLog($message, $level), "Found '{$message}' in the logs.");
    }

    protected function assertLogged($message, $level)
    {
        $this->assertTrue($this->hasLog($message, $level), "Couldn't find '{$message}' in the logs.");
    }

    /**
     * @param $message
     * @param $level
     *
     * @return bool
     */
    protected function hasLog($message, $level)
    {
        return collect($this->app['log']->getMonolog()->getHandlers())->filter(function ($handler) use ($message, $level) {
            return $handler instanceof TestHandler
                && $handler->hasRecordThatContains($message, $level);
        })->count() > 0;
    }
}
