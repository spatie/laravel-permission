<?php

namespace Spatie\Permission\Test;

use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\PermissionServiceProvider;
use File;

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
        $this->testRole = Role::first();
        $this->testPermission = Permission::first();
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
    }

    /**
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

        $user = new User();
        $user->email = 'test@user.com';
        $user->save();

        $role = new Role();
        $role->name = 'testRole';
        $role->save();

        $permission = new Permission();
        $permission->name = 'edit-articles';
        $permission->save();
    }

    protected function initializeDirectory($directory)
    {
        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }
        File::makeDirectory($directory);
    }

    public function getTempDirectory($suffix = '')
    {
        return __DIR__.'/temp'.($suffix == '' ? '' : '/'.$suffix);
    }

    protected function reloadPermissions()
    {
        app(PermissionRegistrar::class)->registerPermissions();
    }
}
