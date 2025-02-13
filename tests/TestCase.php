<?php

namespace Spatie\Permission\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\PassportServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\PermissionServiceProvider;
use Spatie\Permission\Tests\TestModels\Admin;
use Spatie\Permission\Tests\TestModels\Client;
use Spatie\Permission\Tests\TestModels\User;

abstract class TestCase extends Orchestra
{
    /** @var \Spatie\Permission\Tests\TestModels\User */
    protected $testUser;

    /** @var \Spatie\Permission\Tests\TestModels\Admin */
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

    /** @var bool */
    protected $usePassport = false;

    protected Client $testClient;

    protected \Spatie\Permission\Models\Permission $testClientPermission;

    protected \Spatie\Permission\Models\Role $testClientRole;

    protected function setUp(): void
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

        if ($this->usePassport) {
            $this->setUpPassport($this->app);
        }

        $this->setUpRoutes();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (method_exists(AboutCommand::class, 'flushState')) {
            AboutCommand::flushState();
        }
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return $this->getLaravelVersion() < 9 ? [
            PermissionServiceProvider::class,
        ] : [
            PermissionServiceProvider::class,
            PassportServiceProvider::class,
        ];
    }

    /**
     * Set up the environment.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        Model::preventLazyLoading();
        $app['config']->set('permission.register_permission_check_method', true);
        $app['config']->set('permission.teams', $this->hasTeams);
        $app['config']->set('permission.testing', true); // fix sqlite
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
            $app['config']->set('permission.models.permission', \Spatie\Permission\Tests\TestModels\Permission::class);
            $app['config']->set('permission.models.role', \Spatie\Permission\Tests\TestModels\Role::class);
        }
        // Use test User model for users provider
        $app['config']->set('auth.providers.users.model', User::class);

        $app['config']->set('cache.prefix', 'spatie_tests---');
        $app['config']->set('cache.default', getenv('CACHE_DRIVER') ?: 'array');

        // FOR MANUAL TESTING OF ALTERNATE CACHE STORES:
        // $app['config']->set('cache.default', 'array');
        // Laravel supports: array, database, file
        // requires extensions: apc, memcached, redis, dynamodb, octane
    }

    /**
     * Set up the database.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function setUpDatabase($app)
    {
        $schema = $app['db']->connection()->getSchemaBuilder();

        $schema->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->softDeletes();
        });

        $schema->create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
        });

        $schema->create('content', function (Blueprint $table) {
            $table->increments('id');
            $table->string('content');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        if (Cache::getStore() instanceof \Illuminate\Cache\DatabaseStore ||
            $app[PermissionRegistrar::class]->getCacheStore() instanceof \Illuminate\Cache\DatabaseStore) {
            $this->createCacheTable();
        }

        if (! $this->useCustomModels) {
            self::$migration->up();
        } else {
            self::$customMigration->up();

            $schema->table(config('permission.table_names.roles'), function (Blueprint $table) {
                $table->softDeletes();
            });
            $schema->table(config('permission.table_names.permissions'), function (Blueprint $table) {
                $table->softDeletes();
            });
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

    protected function setUpPassport($app): void
    {
        if ($this->getLaravelVersion() < 9) {
            return;
        }

        $app['config']->set('permission.use_passport_client_credentials', true);
        $app['config']->set('auth.guards.api', ['driver' => 'passport', 'provider' => 'users']);

        // mimic passport:install (must load migrations using our own call to loadMigrationsFrom() else rollbacks won't occur, and migrations will be left in skeleton directory
        $this->artisan('passport:keys');
        $this->loadMigrationsFrom(__DIR__.'/../vendor/laravel/passport/database/migrations/');
        $provider = in_array('users', array_keys(config('auth.providers'))) ? 'users' : null;
        $this->artisan('passport:client', ['--personal' => true, '--name' => config('app.name').' Personal Access Client']);
        $this->artisan('passport:client', ['--password' => true, '--name' => config('app.name').' Password Grant Client', '--provider' => $provider]);

        $this->testClient = Client::create(['name' => 'Test', 'redirect' => 'https://example.com', 'personal_access_client' => 0, 'password_client' => 0, 'revoked' => 0]);
        $this->testClientRole = $app[Role::class]->create(['name' => 'clientRole', 'guard_name' => 'api']);
        $this->testClientPermission = $app[Permission::class]->create(['name' => 'edit-posts', 'guard_name' => 'api']);
    }

    private function prepareMigration()
    {
        $migration = str_replace(
            [
                '(\'id\'); // permission id',
                '(\'id\'); // role id',
                'references(\'id\') // permission id',
                'references(\'id\') // role id',
                'bigIncrements',
                'unsignedBigInteger($pivotRole)',
                'unsignedBigInteger($pivotPermission)',
            ],
            [
                '(\'permission_test_id\');',
                '(\'role_test_id\');',
                'references(\'permission_test_id\')',
                'references(\'role_test_id\')',
                'uuid',
                'uuid($pivotRole)->nullable(false)',
                'uuid($pivotPermission)->nullable(false)',
            ],
            file_get_contents(__DIR__.'/../database/migrations/create_permission_tables.php.stub')
        );

        file_put_contents(__DIR__.'/CreatePermissionCustomTables.php', $migration);

        self::$migration = require __DIR__.'/../database/migrations/create_permission_tables.php.stub';

        self::$customMigration = require __DIR__.'/CreatePermissionCustomTables.php';
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

    // //// TEST HELPERS
    public function runMiddleware($middleware, $permission, $guard = null, bool $client = false)
    {
        $request = new Request;
        if ($client) {
            $request->headers->set('Authorization', 'Bearer '.str()->random(30));
        }

        try {
            return $middleware->handle($request, function () {
                return (new Response)->setContent('<html></html>');
            }, $permission, $guard)->status();
        } catch (UnauthorizedException $e) {
            return $e->getStatusCode();
        }
    }

    public function getLastRouteMiddlewareFromRouter($router)
    {
        return last($router->getRoutes()->get())->middleware();
    }

    public function getRouter()
    {
        return app('router');
    }

    public function getRouteResponse()
    {
        return function () {
            return (new Response)->setContent('<html></html>');
        };
    }

    protected function getLaravelVersion()
    {
        return (float) app()->version();
    }
}
