<?php

namespace Spatie\Permission\Tests;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Tests\TestModels\Admin;
use Spatie\Permission\Tests\TestModels\Permission;
use Spatie\Permission\Tests\TestModels\User;

class HasPermissionsWithCustomModelsTest extends HasPermissionsTest
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
    public function it_can_use_custom_model_permission()
    {
        $this->assertSame(get_class($this->testUserPermission), Permission::class);
    }

    /** @test */
    #[Test]
    public function it_can_use_custom_fields_from_cache()
    {
        DB::connection()->getSchemaBuilder()->table(config('permission.table_names.roles'), function ($table) {
            $table->string('type')->default('R');
        });
        DB::connection()->getSchemaBuilder()->table(config('permission.table_names.permissions'), function ($table) {
            $table->string('type')->default('P');
        });

        $this->testUserRole->givePermissionTo($this->testUserPermission);
        app(PermissionRegistrar::class)->getPermissions();

        DB::enableQueryLog();
        $this->assertSame('P', Permission::findByName('edit-articles')->type);
        $this->assertSame('R', Permission::findByName('edit-articles')->roles[0]->type);
        DB::disableQueryLog();

        $this->assertSame(0, count(DB::getQueryLog()));
    }

    /** @test */
    #[Test]
    public function it_can_scope_users_using_a_int()
    {
        // Skipped because custom model uses uuid,
        // replacement "it_can_scope_users_using_a_uuid"
        $this->assertTrue(true);
    }

    /** @test */
    #[Test]
    public function it_can_scope_users_using_a_uuid()
    {
        $uuid1 = $this->testUserPermission->getKey();
        $uuid2 = app(Permission::class)::where('name', 'edit-news')->first()->getKey();

        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->givePermissionTo([$uuid1, $uuid2]);
        $this->testUserRole->givePermissionTo($uuid1);
        $user2->assignRole('testRole');

        $scopedUsers1 = User::permission($uuid1)->get();
        $scopedUsers2 = User::permission([$uuid2])->get();

        $this->assertEquals(2, $scopedUsers1->count());
        $this->assertEquals(1, $scopedUsers2->count());
    }

    /** @test */
    #[Test]
    public function it_doesnt_detach_roles_when_soft_deleting()
    {
        $this->testUserRole->givePermissionTo($this->testUserPermission);

        DB::enableQueryLog();
        $this->testUserPermission->delete();
        DB::disableQueryLog();

        $this->assertSame(1 + $this->resetDatabaseQuery, count(DB::getQueryLog()));

        $permission = Permission::onlyTrashed()->find($this->testUserPermission->getKey());

        $this->assertEquals(1, DB::table(config('permission.table_names.role_has_permissions'))->where('permission_test_id', $permission->getKey())->count());
    }

    /** @test */
    #[Test]
    public function it_doesnt_detach_users_when_soft_deleting()
    {
        $this->testUser->givePermissionTo($this->testUserPermission);

        DB::enableQueryLog();
        $this->testUserPermission->delete();
        DB::disableQueryLog();

        $this->assertSame(1 + $this->resetDatabaseQuery, count(DB::getQueryLog()));

        $permission = Permission::onlyTrashed()->find($this->testUserPermission->getKey());

        $this->assertEquals(1, DB::table(config('permission.table_names.model_has_permissions'))->where('permission_test_id', $permission->getKey())->count());
    }

    /** @test */
    #[Test]
    public function it_does_detach_roles_and_users_when_force_deleting()
    {
        $permission_id = $this->testUserPermission->getKey();
        $this->testUserRole->givePermissionTo($permission_id);
        $this->testUser->givePermissionTo($permission_id);

        DB::enableQueryLog();
        $this->testUserPermission->forceDelete();
        DB::disableQueryLog();

        $this->assertSame(3 + $this->resetDatabaseQuery, count(DB::getQueryLog())); // avoid detach permissions on permissions

        $permission = Permission::withTrashed()->find($permission_id);

        $this->assertNull($permission);
        $this->assertEquals(0, DB::table(config('permission.table_names.role_has_permissions'))->where('permission_test_id', $permission_id)->count());
        $this->assertEquals(0, DB::table(config('permission.table_names.model_has_permissions'))->where('permission_test_id', $permission_id)->count());
    }

    /** @test */
    #[Test]
    public function it_should_touch_when_assigning_new_permissions()
    {
        Carbon::setTestNow('2021-07-19 10:13:14');

        $user = Admin::create(['email' => 'user1@test.com']);
        $permission1 = Permission::create(['name' => 'edit-news', 'guard_name' => 'admin']);
        $permission2 = Permission::create(['name' => 'edit-blog', 'guard_name' => 'admin']);

        $this->assertSame('2021-07-19 10:13:14', $permission1->updated_at->format('Y-m-d H:i:s'));

        Carbon::setTestNow('2021-07-20 19:13:14');

        $user->syncPermissions([$permission1->getKey(), $permission2->getKey()]);

        $this->assertSame('2021-07-20 19:13:14', $permission1->refresh()->updated_at->format('Y-m-d H:i:s'));
        $this->assertSame('2021-07-20 19:13:14', $permission2->refresh()->updated_at->format('Y-m-d H:i:s'));
    }
}
