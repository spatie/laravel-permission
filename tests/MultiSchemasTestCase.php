<?php

namespace Spatie\Permission\Tests;

use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Schema\Blueprint;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Tests\TestModels\MultiSchemas\App1;
use Spatie\Permission\Tests\TestModels\MultiSchemas\App2;

abstract class MultiSchemasTestCase extends TestCase
{
    protected App1\User $testUserApp1;

    protected App2\Customer $testCustomerApp2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton('PermissionRegistrarApp1', fn (Application $app) => new PermissionRegistrar(
            config: array_replace_recursive(config('permission'), [
                'models' => [
                    'permission' => App1\Permission::class,
                    'role' => App1\Role::class,
                ],
                'cache' => [
                    'key' => 'spatie.permission.cache.app1'
                ],
            ]),
            cacheManager: $app->make(CacheManager::class)
        ));

        $this->app->singleton('PermissionRegistrarApp2', fn (Application $app) => new PermissionRegistrar(
            config: array_replace_recursive(config('permission'), [
                'models' => [
                    'permission' => App2\Permission::class,
                    'role' => App2\Role::class,
                ],
                'cache' => [
                    'key' => 'spatie.permission.cache.app2'
                ],
            ]),
            cacheManager: $app->make(CacheManager::class)
        ));
    }

    /**
     * Set up the environment.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('database.connections.sqlite2', array_merge($app['config']->get('database.connections.sqlite'), []));
    }

    /**
     * @inheritdoc
     */
    protected function setUpDatabase($app)
    {
        parent::setUpDatabase($app);

        $originalDatabaseDefault = $app['config']->get('database.default');

        // [start switch configs]

        $app['config']->set('database.default', 'sqlite2');

        $schema = $app['db']->connection()->getSchemaBuilder();

        $schema->create('customers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->softDeletes();
        });

        self::$migration->up();

        $app['config']->set('database.default', $originalDatabaseDefault);

        // [end switch]

        $this->testUserApp1 = App1\User::create(['email' => 'test@user-app-1.com']);
        $this->testCustomerApp2 = App2\Customer::create(['email' => 'test@customer-app-2.com']);
    }
}
