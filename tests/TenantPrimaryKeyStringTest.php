<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Exceptions\TenantAlreadyExist;
use Spatie\Permission\Exceptions\TenantDoesNotExist;
use Spatie\Permission\Contracts\Tenant;

class TenantPrimaryKeyStringTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        parent::setUpDatabase($app);

        $app[Tenant::class]->create(['tenant_name' => 'testTenant', 'app_code' => 'FOOBAR']);
        $app[Tenant::class]->create(['tenant_name' => 'testTenant2', 'app_code' => 'BARFOO']);
    }


    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('permission.foreign_keys.tenants', [
            'id'   => 'app_code',
            'key_type'   => 'string',
            'str_length'   => '6',
        ]);

        $app['config']->set('permission.table_names.tenants', 'applications');

        parent::getEnvironmentSetUp($app);
    }

    /** @test */
    public function it_throws_an_exception_when_the_tentant_already_exists()
    {
        $this->expectException(TenantAlreadyExist::class);

        app(Tenant::class)->create(['tenant_name' => 'tmpTentant', 'app_code' => 'ABCDEF']);
        app(Tenant::class)->create(['tenant_name' => 'tmpTenant2', 'app_code' => 'ABCDEF']);
    }

    /** @test */
    public function it_throws_an_exception_when_the_tenant_id_that_is_a_string_can_not_be_found()
    {
        $this->expectException(TenantDoesNotExist::class);

        app(Tenant::class)->findById('FOO');
    }

    /** @test */
    public function it_can_return_a_tenant_object_when_the_primary_key_is_a_string()
    {
        $tenant = app(Tenant::class)->findById('FOOBAR');
        $this->assertEquals(1, count($tenant));
        $this->assertInstanceOf('\Spatie\Permission\Models\Tenant', $tenant);
    }
}
