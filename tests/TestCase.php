<?php

namespace Spatie\Permission\Test;

use File;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\PermissionRegistrar;
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
        $this->initializeDirectory($this->getTempDirectory());

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => $this->getTempDirectory().'/database.sqlite',
            'prefix' => '',
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
        file_put_contents($this->getTempDirectory().'/database.sqlite', null);

        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
        });

        include_once '__DIR__'.'/../resources/migrations/create_permission_tables.php.stub';

        (new \CreatePermissionTables())->up();

        User::create(['email' => 'test@user.com']);
        $app[Role::class]->create(['name' => 'testRole']);
        $app[Permission::class]->create(['name' => 'edit-articles']);
        $app[Permission::class]->create(['name' => 'edit-news']);
    }

    /**
     * Initialize the directory.
     *
     * @param string $directory
     */
    protected function initializeDirectory($directory)
    {
        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }
        if (!File::exists($directory)) {
            File::makeDirectory($directory);
        }
    }

    /**
     * Get the temporary directory.
     *
     * @param string $suffix
     *
     * @return string
     */
    public function getTempDirectory($suffix = '')
    {
        return __DIR__.'/temp'.($suffix == '' ? '' : '/'.$suffix);
    }

    /**
     * Reload the permissions.
     *
     * @return bool
     */
    protected function reloadPermissions()
    {
        return app(PermissionRegistrar::class)->registerPermissions();
    }

    /**
     * Refresh the testuser.
     */
    public function refreshTestUser()
    {
        $this->testUser = User::find($this->testUser->id);
    }
}
