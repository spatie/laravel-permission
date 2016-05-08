<?php namespace Spatie\Permission\Test;

use Spatie\Permission\Models\Permission;

class RoleTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        
        Permission::create(['name' => 'other-permission']);

        $this->testRole->givePermissionTo($this->testPermission);
    }

    /** @test */
    public function it_returns_true_if_role_has_permission()
    {
        $this->assertTrue($this->testRole->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_returns_false_if_role_has_not_permission()
    {
        $this->assertFalse($this->testRole->hasPermissionTo('other-permission'));
    }

    /** @test */
    public function it_allows_permission_models_to_be_passed_in()
    {
        $permission = app(Permission::class)->findByName('edit-articles');

        $this->assertTrue($this->testRole->hasPermissionTo($permission));

        $permission = app(Permission::class)->findByName('other-permission');

        $this->assertFalse($this->testRole->hasPermissionTo($permission));
    }
}

