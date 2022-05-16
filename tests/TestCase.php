<?php

namespace Spatie\Permission\Test;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\PermissionServiceProvider;

abstract class TestCase extends Orchestra
{
    /** @var \Spatie\Permission\Test\User */
    protected $testUser;

    /** @var \Spatie\Permission\Test\Admin */
    protected $testAdmin;

    /** @var \Spatie\Permission\Models\Role */
    protected $testUserRole;

    /** @var \Spatie\Permission\Models\Role */
    protected $testAdminRole;

    /** @var \Spatie\Permission\Models\Permission */
    protected $testUserPermission;

    /** @var \Spatie\Permission\Models\Permission */
    protected $testAdminPermission;

    /** @var bool */
    protected $useCustomModels = false;

    /** @var bool */
    protected $hasTeams = false;

    protected static $migration;
    protected static $customMigration;

    public function setUp(): void
    {
        parent::setUp();

        if (! self::$migration) {
            $this->prepareMigration();
        }

        // Note: this also flushes the cache from within the migration
        $this->setUpDatabase($this->app);
        if ($this->hasTeams) {
            setPermissionsTeamId(1);
        }

        $this->setUpRoutes();
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
        $app['config']->set('permission.register_permission_check_method', true);
        $app['config']->set('permission.teams', $this->hasTeams);
        $app['config']->set('permission.testing', true); //fix sqlite
        $app['config']->set('permission.column_names.model_morph_key', 'model_test_id');
        $app['config']->set('permission.column_names.team_foreign_key', 'team_test_id');
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('permission.column_names.role_pivot_key', 'role_test_id');
        $app['config']->set('permission.column_names.permission_pivot_key', 'permission_test_id');
        $app['config']->set('view.paths', [__DIR__.'/resources/views']);

        // ensure api guard exists (required since Laravel 8.55)
        $app['config']->set('auth.guards.api', ['driver' => 'session', 'provider' => 'users']);

        // Set-up admin guard
        $app['config']->set('auth.guards.admin', ['driver' => 'session', 'provider' => 'admins']);
        $app['config']->set('auth.providers.admins', ['driver' => 'eloquent', 'model' => Admin::class]);
        if ($this->useCustomModels) {
            $app['config']->set('permission.models.permission', \Spatie\Permission\Test\Permission::class);
            $app['config']->set('permission.models.role', \Spatie\Permission\Test\Role::class);
        }
        // Use test User model for users provider
        $app['config']->set('auth.providers.users.model', User::class);

        $app['config']->set('cache.prefix', 'spatie_tests---');
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

        $app['db']->connection()->getSchemaBuilder()->create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
        });

        if (Cache::getStore() instanceof \Illuminate\Cache\DatabaseStore ||
            $app[PermissionRegistrar::class]->getCacheStore() instanceof \Illuminate\Cache\DatabaseStore) {
            $this->createCacheTable();
        }

        if (! $this->useCustomModels) {
            self::$migration->up();
        } else {
            self::$customMigration->up();
        }

        $this->testUser = User::create(['email' => 'test@user.com']);
        $this->testAdmin = Admin::create(['email' => 'admin@user.com']);
        $this->testUserRole = $app[Role::class]->create(['name' => 'testRole']);
        $app[Role::class]->create(['name' => 'testRole2']);
        $this->testAdminRole = $app[Role::class]->create(['name' => 'testAdminRole', 'guard_name' => 'admin']);
        $this->testUserPermission = $app[Permission::class]->create(['name' => 'edit-articles']);
        $app[Permission::class]->create(['name' => 'edit-news']);
        $app[Permission::class]->create(['name' => 'edit-blog']);
        $this->testAdminPermission = $app[Permission::class]->create(['name' => 'admin-permission', 'guard_name' => 'admin']);
        $app[Permission::class]->create(['name' => 'Edit News']);
    }

    private function prepareMigration()
    {
        $migration = str_replace(
            [
                'CreatePermissionTables',
                '(\'id\'); // permission id',
                '(\'id\'); // role id',
                'references(\'id\') // permission id',
                'references(\'id\') // role id',
            ],
            [
                'CreatePermissionCustomTables',
                '(\'permission_test_id\');',
                '(\'role_test_id\');',
                'references(\'permission_test_id\')',
                'references(\'role_test_id\')',
            ],
            file_get_contents(__DIR__.'/../database/migrations/create_permission_tables.php.stub')
        );

        file_put_contents(__DIR__.'/CreatePermissionCustomTables.php', $migration);

        include_once __DIR__.'/../database/migrations/create_permission_tables.php.stub';
        self::$migration = new \CreatePermissionTables();

        include_once __DIR__.'/CreatePermissionCustomTables.php';
        self::$customMigration = new \CreatePermissionCustomTables();
    }

    protected function reloadPermissions()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function createCacheTable()
    {
        Schema::create('cache', function ($table) {
            $table->string('key')->unique();
            $table->text('value');
            $table->integer('expiration');
        });
    }

    /**
     * Create routes to test authentication with guards.
     */
    public function setUpRoutes(): void
    {
        Route::middleware('auth:api')->get('/check-api-guard-permission', function (Request $request) {
            return [
                 'status' => $request->user()->hasPermissionTo('do_that'),
             ];
        });
    }
}
