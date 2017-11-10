<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Exceptions\TenantAlreadyExist;
use Spatie\Permission\Exceptions\TenantDoesNotExist;
use Spatie\Permission\Contracts\Tenant;

class TenantTest extends TestCase
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

        $app[Tenant::class]->create(['tenant_name' => 'testTenant']);
        $app[Tenant::class]->create(['tenant_name' => 'testTenant2']);
    }

    /** @test */
    public function it_throws_an_exception_when_the_tentant_already_exists()
    {
        $this->expectException(TenantAlreadyExist::class);

        app(Tenant::class)->create(['tenant_name' => 'tmpTentant', 'id' => 1]);
        app(Tenant::class)->create(['tenant_name' => 'tmpTenant2', 'id' => 1]);
    }

    /** @test */
    public function it_throws_an_exception_when_the_tenant_id_that_is_an_int_can_not_be_found()
    {
        $this->expectException(TenantDoesNotExist::class);

        app(Tenant::class)->findById(15);
    }

    /** @test */
    public function it_can_return_a_tenant_object()
    {
        $tenant = app(Tenant::class)->findById(1);
        $this->assertInstanceOf('\Spatie\Permission\Models\Tenant', $tenant);
    }
}
