<?php

namespace Spatie\Permission\Tests;

use Illuminate\Database\Schema\Blueprint;
use Spatie\Permission\Tests\TestModels\MultiSchemas\App1;
use Spatie\Permission\Tests\TestModels\MultiSchemas\App2;

abstract class MultiSchemasTestCase extends TestCase
{
    protected App1\User $testUserApp1;

    protected App2\Customer $testCustomerApp2;

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
