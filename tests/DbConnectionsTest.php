<?php

namespace Spatie\Permission\Test;

class DbConnectionsTest extends TestCase
{
    protected $first_connection = 'will be detected by tests';

    protected $second_connection = 'second_connection';

    /**
     * Here we add another connection to the environment setup
     * And then the master setUp() will seed the database using the new connection, in preparation for these tests.
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // remember the default for use in test assertions
        $this->first_connection = $app['config']['database.default'];

        // create another connection for testing
        $app['config']->set('permission.db_connection', $this->second_connection);
        $app['config']->set('database.connections.'.$this->second_connection, [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => 'connection2_',
        ]);

        // pass this preferred connection to rest of setUp()
        $this->connection = $this->second_connection;
    }

    /** @test */
    public function the_testsetup_roles_are_found_in_the_second_connection()
    {
        $this->assertDatabaseHas('roles', ['name' => 'testRole'], $this->second_connection);
        $this->assertDatabaseHas('permissions', ['name' => 'edit-articles'], $this->second_connection);
    }

    /** @test */
    public function the_testsetup_roles_are_not_found_in_the_default_connection()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->expectExceptionMessage('no such table');
        $this->assertDatabaseMissing('roles', ['name' => 'testRole'], $this->first_connection);
        $this->assertDatabaseMissing('permissions', ['name' => 'edit-articles'], $this->first_connection);
    }

    public function the_test_user_is_found_in_the_second_connection()
    {
        $this->assertDatabaseHas('users', ['email' => 'test@user.com'], $this->second_connection);
    }

    public function the_test_user_is_not_found_in_the_default_connection()
    {
        $this->assertDatabaseMissing('users', ['email' => 'test@user.com'], $this->first_connection);
    }
}
