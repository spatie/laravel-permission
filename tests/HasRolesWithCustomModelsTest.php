<?php

namespace Spatie\Permission\Tests;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Tests\TestModels\Admin;
use Spatie\Permission\Tests\TestModels\Role;

class HasRolesWithCustomModelsTest extends HasRolesTest
{
    /** @var bool */
    protected $useCustomModels = true;

    /** @var int */
    protected $resetDatabaseQuery = 0;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        if ($app['config']->get('cache.default') == 'database') {
            $this->resetDatabaseQuery = 1;
        }
    }

    /** @test */
    #[Test]
    public function it_can_use_custom_model_role()
    {
        $this->assertSame(get_class($this->testUserRole), Role::class);
    }

    /** @test */
    #[Test]
    public function it_doesnt_detach_permissions_when_soft_deleting()
    {
        $this->testUserRole->givePermissionTo($this->testUserPermission);

        DB::enableQueryLog();
        $this->testUserRole->delete();
        DB::disableQueryLog();

        $this->assertSame(1 + $this->resetDatabaseQuery, count(DB::getQueryLog()));

        $role = Role::onlyTrashed()->find($this->testUserRole->getKey());

        $this->assertEquals(1, DB::table(config('permission.table_names.role_has_permissions'))->where('role_test_id', $role->getKey())->count());
    }

    /** @test */
    #[Test]
    public function it_doesnt_detach_users_when_soft_deleting()
    {
        $this->testUser->assignRole($this->testUserRole);

        DB::enableQueryLog();
        $this->testUserRole->delete();
        DB::disableQueryLog();

        $this->assertSame(1 + $this->resetDatabaseQuery, count(DB::getQueryLog()));

        $role = Role::onlyTrashed()->find($this->testUserRole->getKey());

        $this->assertEquals(1, DB::table(config('permission.table_names.model_has_roles'))->where('role_test_id', $role->getKey())->count());
    }

    /** @test */
    #[Test]
    public function it_does_detach_permissions_and_users_when_force_deleting()
    {
        $role_id = $this->testUserRole->getKey();
        $this->testUserPermission->assignRole($role_id);
        $this->testUser->assignRole($role_id);

        DB::enableQueryLog();
        $this->testUserRole->forceDelete();
        DB::disableQueryLog();

        $this->assertSame(3 + $this->resetDatabaseQuery, count(DB::getQueryLog()));

        $role = Role::withTrashed()->find($role_id);

        $this->assertNull($role);
        $this->assertEquals(0, DB::table(config('permission.table_names.role_has_permissions'))->where('role_test_id', $role_id)->count());
        $this->assertEquals(0, DB::table(config('permission.table_names.model_has_roles'))->where('role_test_id', $role_id)->count());
    }

    /** @test */
    #[Test]
    public function it_should_touch_when_assigning_new_roles()
    {
        Carbon::setTestNow('2021-07-19 10:13:14');

        $user = Admin::create(['email' => 'user1@test.com']);
        $role1 = app(Role::class)->create(['name' => 'testRoleInWebGuard', 'guard_name' => 'admin']);
        $role2 = app(Role::class)->create(['name' => 'testRoleInWebGuard1', 'guard_name' => 'admin']);

        $this->assertSame('2021-07-19 10:13:14', $role1->updated_at->format('Y-m-d H:i:s'));

        Carbon::setTestNow('2021-07-20 19:13:14');

        $user->syncRoles([$role1->getKey(), $role2->getKey()]);

        $this->assertSame('2021-07-20 19:13:14', $role1->refresh()->updated_at->format('Y-m-d H:i:s'));
        $this->assertSame('2021-07-20 19:13:14', $role2->refresh()->updated_at->format('Y-m-d H:i:s'));
    }
    
    /** @test */
    #[Test]
    public function it_can_assign_and_remove_a_role_with_custom_identifier()
    {
        config(['permission.role_identifier' => 'slug']);

        app(Role::class)->create(['name' => 'Editor', 'slug' => 'editor', 'guard_name' => 'web']);
        app(Role::class)->create(['name' => 'Writer', 'slug' => 'writer', 'guard_name' => 'web']);

        $this->assertFalse($this->testUser->hasRole('editor'));
        $this->assertFalse($this->testUser->hasRole('Editor'));

        $this->testUser->assignRole('editor');
        $this->testUser->assignRole('writer');

        $this->assertTrue($this->testUser->hasRole('editor'));
        $this->assertFalse($this->testUser->hasRole('Editor'));

        $this->assertTrue($this->testUser->hasAllRoles(['editor']));
        $this->assertTrue($this->testUser->hasAllRoles(['editor', 'writer']));
        $this->assertFalse($this->testUser->hasAllRoles(['editor', 'writer', 'not exist']));

        $this->assertTrue($this->testUser->hasExactRoles(['editor', 'writer']));
        $this->assertFalse($this->testUser->hasExactRoles(['editor']));

        $this->testUser->removeRole('editor');

        $this->assertFalse($this->testUser->hasRole('editor'));
    }
    
    /** @test */
    #[Test]
    public function it_throws_an_exception_when_assigning_a_role_by_name_when_identifier_is_not_name()
    {
        config(['permission.role_identifier' => 'slug']);

        app(Role::class)->create(['name' => 'Editor', 'slug' => 'editor', 'guard_name' => 'web']);

        $this->expectException(RoleDoesNotExist::class);

        $this->testUser->assignRole('Editor');
    }
}
