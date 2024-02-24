<?php

namespace Spatie\Permission\Tests;

use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\PermissionRegistrar;

class MultiSchemasHasRolesTest extends MultiSchemasTestCase
{
    /**
     * @test
     */
    public function it_can_manage_roles_and_permissions_on_multiple_schemas_without_switch_configuration()
    {
        $roleApp1Name = 'testRoleApp1InWebGuard';
        $roleApp2Name = 'testRoleApp2InWebGuard';
        $permissionApp1Name = 'testPermissionApp1InWebGuard';
        $permissionApp2Name = 'testPermissionApp2InWebGuard';

        /** @var PermissionRegistrar $permissionRegistrarApp1 */
        $permissionRegistrarApp1 = $this->app->make('PermissionRegistrarApp1');
        /** @var Role $roleClassApp1 */
        $roleClassApp1 = $permissionRegistrarApp1->getRoleClass();
        /** @var Permission $permissionClassApp1 */
        $permissionClassApp1 = $permissionRegistrarApp1->getPermissionClass();

        /** @var PermissionRegistrar $permissionRegistrarApp2 */
        $permissionRegistrarApp2 = $this->app->make('PermissionRegistrarApp2');
        /** @var Role $roleClassApp2 */
        $roleClassApp2 = $permissionRegistrarApp2->getRoleClass();
        /** @var Permission $permissionClassApp2 */
        $permissionClassApp2 = $permissionRegistrarApp2->getPermissionClass();

        $this->assertFalse($this->testUserApp1->hasRole($roleApp1Name));
        $this->assertFalse($this->testCustomerApp2->hasRole($roleApp2Name));

        $roleApp1 = $roleClassApp1::findOrCreate($roleApp1Name, 'web');
        $roleApp2 = $roleClassApp2::findOrCreate($roleApp2Name, 'web');

        $permissionApp1 = $permissionClassApp1::findOrCreate($permissionApp1Name, 'web');
        $permissionApp2 = $permissionClassApp2::findOrCreate($permissionApp2Name, 'web');

        $roleApp1->givePermissionTo([$permissionApp1Name]);
        $roleApp2->givePermissionTo([$permissionApp2Name]);

        $this->assertTrue($roleApp1->hasPermissionTo($permissionApp1));
        $this->assertTrue($roleApp2->hasPermissionTo($permissionApp2));

        $this->assertTrue($roleApp1->hasPermissionTo($permissionApp1Name));
        $this->assertTrue($roleApp2->hasPermissionTo($permissionApp2Name));

        $this->assertFalse($this->testUserApp1->hasRole($roleApp1));
        $this->assertFalse($this->testCustomerApp2->hasRole($roleApp2));

        $this->assertFalse($this->testUserApp1->hasRole($roleApp1Name));
        $this->assertFalse($this->testCustomerApp2->hasRole($roleApp2Name));

        $this->testUserApp1->assignRole($roleApp1Name);
        $this->assertTrue($this->testUserApp1->hasRole($roleApp1Name));

        $this->testCustomerApp2->assignRole($roleApp2Name);
        $this->assertTrue($this->testCustomerApp2->hasRole($roleApp2Name));

        $this->testUserApp1->unsetRelation('roles');
        $this->testCustomerApp2->unsetRelation('roles');

        $this->assertTrue($this->testUserApp1->hasRole($roleApp1Name));
        $this->assertTrue($this->testCustomerApp2->hasRole($roleApp2Name));

        $this->assertTrue($this->testUserApp1->hasPermissionTo($permissionApp1Name));
        $this->assertTrue($this->testUserApp1->hasPermissionTo($permissionApp1));
        $this->assertTrue($this->testUserApp1->can($permissionApp1Name));
        $this->assertFalse($this->testUserApp1->checkPermissionTo($permissionApp2Name));
        $this->assertFalse($this->testUserApp1->checkPermissionTo($permissionApp2));

        $this->assertTrue($this->testCustomerApp2->hasPermissionTo($permissionApp2Name));
        $this->assertTrue($this->testCustomerApp2->hasPermissionTo($permissionApp2));
        $this->assertFalse($this->testCustomerApp2->checkPermissionTo($permissionApp1Name));
        $this->assertFalse($this->testCustomerApp2->checkPermissionTo($permissionApp1));
    }
}
