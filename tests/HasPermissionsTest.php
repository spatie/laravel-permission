<?php

namespace Spatie\Permission\Tests;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Events\PermissionAttached;
use Spatie\Permission\Events\PermissionDetached;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Tests\TestModels\SoftDeletingUser;
use Spatie\Permission\Tests\TestModels\User;

class HasPermissionsTest extends TestCase
{
    /** @test */
    #[Test]
    public function it_can_assign_a_permission_to_a_user()
    {
        $this->testUser->givePermissionTo($this->testUserPermission);

        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission));
    }

    /** @test */
    #[Test]
    public function it_can_assign_a_permission_to_a_user_with_a_non_default_guard()
    {
        $testUserPermission = app(Permission::class)->create([
            'name' => 'edit-articles',
            'guard_name' => 'api',
        ]);

        $this->testUser->givePermissionTo($testUserPermission);

        $this->assertTrue($this->testUser->hasPermissionTo($testUserPermission));
    }

    /** @test */
    #[Test]
    public function it_throws_an_exception_when_assigning_a_permission_that_does_not_exist()
    {
        $this->expectException(PermissionDoesNotExist::class);

        $this->testUser->givePermissionTo('permission-does-not-exist');
    }

    /** @test */
    #[Test]
    public function it_throws_an_exception_when_assigning_a_permission_to_a_user_from_a_different_guard()
    {
        $this->expectException(GuardDoesNotMatch::class);

        $this->testUser->givePermissionTo($this->testAdminPermission);

        $this->expectException(PermissionDoesNotExist::class);

        $this->testUser->givePermissionTo('admin-permission');
    }

    /** @test */
    #[Test]
    public function it_can_revoke_a_permission_from_a_user()
    {
        $this->testUser->givePermissionTo($this->testUserPermission);

        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission));

        $this->testUser->revokePermissionTo($this->testUserPermission);

        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission));
    }

    /**
     * @test
     *
     * @requires PHP >= 8.1
     */
    #[RequiresPhp('>= 8.1')]
    #[Test]
    public function it_can_assign_and_remove_a_permission_using_enums()
    {
        $enum = TestModels\TestRolePermissionsEnum::VIEWARTICLES;

        $permission = app(Permission::class)->findOrCreate($enum->value, 'web');

        $this->testUser->givePermissionTo($enum);

        $this->assertTrue($this->testUser->hasPermissionTo($enum));
        $this->assertTrue($this->testUser->hasAnyPermission($enum));
        $this->assertTrue($this->testUser->hasDirectPermission($enum));

        $this->testUser->revokePermissionTo($enum);

        $this->assertFalse($this->testUser->hasPermissionTo($enum));
        $this->assertFalse($this->testUser->hasAnyPermission($enum));
        $this->assertFalse($this->testUser->hasDirectPermission($enum));
    }

    /**
     * @test
     *
     * @requires PHP >= 8.1
     */
    #[RequiresPhp('>= 8.1')]
    #[Test]
    public function it_can_scope_users_using_enums()
    {
        $enum1 = TestModels\TestRolePermissionsEnum::VIEWARTICLES;
        $enum2 = TestModels\TestRolePermissionsEnum::EDITARTICLES;
        $permission1 = app(Permission::class)->findOrCreate($enum1->value, 'web');
        $permission2 = app(Permission::class)->findOrCreate($enum2->value, 'web');

        User::all()->each(fn ($item) => $item->delete());
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user3 = User::create(['email' => 'user3@test.com']);
        $user1->givePermissionTo([$enum1, $enum2]);
        $this->testUserRole->givePermissionTo($enum2);
        $user2->assignRole('testRole');

        $scopedUsers1 = User::permission($enum2)->get();
        $scopedUsers2 = User::permission([$enum1])->get();
        $scopedUsers3 = User::withoutPermission([$enum1])->get();
        $scopedUsers4 = User::withoutPermission([$enum2])->get();

        $this->assertEquals(2, $scopedUsers1->count());
        $this->assertEquals(1, $scopedUsers2->count());
        $this->assertEquals(2, $scopedUsers3->count());
        $this->assertEquals(1, $scopedUsers4->count());
    }

    /** @test */
    #[Test]
    public function it_can_scope_users_using_a_string()
    {
        User::all()->each(fn ($item) => $item->delete());
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user3 = User::create(['email' => 'user3@test.com']);
        $user1->givePermissionTo(['edit-articles', 'edit-news']);
        $this->testUserRole->givePermissionTo('edit-articles');
        $user2->assignRole('testRole');

        $scopedUsers1 = User::permission('edit-articles')->get();
        $scopedUsers2 = User::permission(['edit-news'])->get();
        $scopedUsers3 = User::withoutPermission('edit-news')->get();

        $this->assertEquals(2, $scopedUsers1->count());
        $this->assertEquals(1, $scopedUsers2->count());
        $this->assertEquals(2, $scopedUsers3->count());
    }

    /** @test */
    #[Test]
    public function it_can_scope_users_using_a_int()
    {
        User::all()->each(fn ($item) => $item->delete());
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user3 = User::create(['email' => 'user3@test.com']);
        $user1->givePermissionTo([1, 2]);
        $this->testUserRole->givePermissionTo(1);
        $user2->assignRole('testRole');

        $scopedUsers1 = User::permission(1)->get();
        $scopedUsers2 = User::permission([2])->get();
        $scopedUsers3 = User::withoutPermission([2])->get();

        $this->assertEquals(2, $scopedUsers1->count());
        $this->assertEquals(1, $scopedUsers2->count());
        $this->assertEquals(2, $scopedUsers3->count());
    }

    /** @test */
    #[Test]
    public function it_can_scope_users_using_an_array()
    {
        User::all()->each(fn ($item) => $item->delete());
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user3 = User::create(['email' => 'user3@test.com']);
        $user1->givePermissionTo(['edit-articles', 'edit-news']);
        $this->testUserRole->givePermissionTo('edit-articles');
        $user2->assignRole('testRole');
        $user3->assignRole('testRole2');

        $scopedUsers1 = User::permission(['edit-articles', 'edit-news'])->get();
        $scopedUsers2 = User::permission(['edit-news'])->get();
        $scopedUsers3 = User::withoutPermission(['edit-news'])->get();

        $this->assertEquals(2, $scopedUsers1->count());
        $this->assertEquals(1, $scopedUsers2->count());
        $this->assertEquals(2, $scopedUsers3->count());
    }

    /** @test */
    #[Test]
    public function it_can_scope_users_using_a_collection()
    {
        User::all()->each(fn ($item) => $item->delete());
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user3 = User::create(['email' => 'user3@test.com']);
        $user1->givePermissionTo(['edit-articles', 'edit-news']);
        $this->testUserRole->givePermissionTo('edit-articles');
        $user2->assignRole('testRole');
        $user3->assignRole('testRole2');

        $scopedUsers1 = User::permission(collect(['edit-articles', 'edit-news']))->get();
        $scopedUsers2 = User::permission(collect(['edit-news']))->get();
        $scopedUsers3 = User::withoutPermission(collect(['edit-news']))->get();

        $this->assertEquals(2, $scopedUsers1->count());
        $this->assertEquals(1, $scopedUsers2->count());
        $this->assertEquals(2, $scopedUsers3->count());
    }

    /** @test */
    #[Test]
    public function it_can_scope_users_using_an_object()
    {
        User::all()->each(fn ($item) => $item->delete());
        $user1 = User::create(['email' => 'user1@test.com']);
        $user1->givePermissionTo($this->testUserPermission->name);

        $scopedUsers1 = User::permission($this->testUserPermission)->get();
        $scopedUsers2 = User::permission([$this->testUserPermission])->get();
        $scopedUsers3 = User::permission(collect([$this->testUserPermission]))->get();
        $scopedUsers4 = User::withoutPermission(collect([$this->testUserPermission]))->get();

        $this->assertEquals(1, $scopedUsers1->count());
        $this->assertEquals(1, $scopedUsers2->count());
        $this->assertEquals(1, $scopedUsers3->count());
        $this->assertEquals(0, $scopedUsers4->count());
    }

    /** @test */
    #[Test]
    public function it_can_scope_users_without_direct_permissions_only_role()
    {
        User::all()->each(fn ($item) => $item->delete());
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user3 = User::create(['email' => 'user3@test.com']);
        $this->testUserRole->givePermissionTo('edit-articles');
        $user1->assignRole('testRole');
        $user2->assignRole('testRole');
        $user3->assignRole('testRole2');

        $scopedUsers1 = User::permission('edit-articles')->get();
        $scopedUsers2 = User::withoutPermission('edit-articles')->get();

        $this->assertEquals(2, $scopedUsers1->count());
        $this->assertEquals(1, $scopedUsers2->count());
    }

    /** @test */
    #[Test]
    public function it_can_scope_users_with_only_direct_permission()
    {
        User::all()->each(fn ($item) => $item->delete());
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user3 = User::create(['email' => 'user3@test.com']);
        $user1->givePermissionTo(['edit-news']);
        $user2->givePermissionTo(['edit-articles', 'edit-news']);

        $scopedUsers1 = User::permission('edit-news')->get();
        $scopedUsers2 = User::withoutPermission('edit-news')->get();

        $this->assertEquals(2, $scopedUsers1->count());
        $this->assertEquals(1, $scopedUsers2->count());
    }

    /** @test */
    #[Test]
    public function it_throws_an_exception_when_calling_hasPermissionTo_with_an_invalid_type()
    {
        $user = User::create(['email' => 'user1@test.com']);

        $this->expectException(PermissionDoesNotExist::class);

        $user->hasPermissionTo(new \stdClass);
    }

    /** @test */
    #[Test]
    public function it_throws_an_exception_when_calling_hasPermissionTo_with_null()
    {
        $user = User::create(['email' => 'user1@test.com']);

        $this->expectException(PermissionDoesNotExist::class);

        $user->hasPermissionTo(null);
    }

    /** @test */
    #[Test]
    public function it_throws_an_exception_when_calling_hasDirectPermission_with_an_invalid_type()
    {
        $user = User::create(['email' => 'user1@test.com']);

        $this->expectException(PermissionDoesNotExist::class);

        $user->hasDirectPermission(new \stdClass);
    }

    /** @test */
    #[Test]
    public function it_throws_an_exception_when_calling_hasDirectPermission_with_null()
    {
        $user = User::create(['email' => 'user1@test.com']);

        $this->expectException(PermissionDoesNotExist::class);

        $user->hasDirectPermission(null);
    }

    /** @test */
    #[Test]
    public function it_throws_an_exception_when_trying_to_scope_a_non_existing_permission()
    {
        $this->expectException(PermissionDoesNotExist::class);

        User::permission('not defined permission')->get();

        $this->expectException(PermissionDoesNotExist::class);

        User::withoutPermission('not defined permission')->get();
    }

    /** @test */
    #[Test]
    public function it_throws_an_exception_when_trying_to_scope_a_permission_from_another_guard()
    {
        $this->expectException(PermissionDoesNotExist::class);

        User::permission('testAdminPermission')->get();

        $this->expectException(PermissionDoesNotExist::class);

        User::withoutPermission('testAdminPermission')->get();

        $this->expectException(GuardDoesNotMatch::class);

        User::permission($this->testAdminPermission)->get();

        $this->expectException(GuardDoesNotMatch::class);

        User::withoutPermission($this->testAdminPermission)->get();
    }

    /** @test */
    #[Test]
    public function it_doesnt_detach_permissions_when_user_soft_deleting()
    {
        $user = SoftDeletingUser::create(['email' => 'test@example.com']);
        $user->givePermissionTo(['edit-news']);
        $user->delete();

        $user = SoftDeletingUser::withTrashed()->find($user->id);

        $this->assertTrue($user->hasPermissionTo('edit-news'));
    }

    /** @test */
    #[Test]
    public function it_can_give_and_revoke_multiple_permissions()
    {
        $this->testUserRole->givePermissionTo(['edit-articles', 'edit-news']);

        $this->assertEquals(2, $this->testUserRole->permissions()->count());

        $this->testUserRole->revokePermissionTo(['edit-articles', 'edit-news']);

        $this->assertEquals(0, $this->testUserRole->permissions()->count());
    }

    /** @test */
    #[Test]
    public function it_can_give_and_revoke_permissions_models_array()
    {
        $models = [app(Permission::class)::where('name', 'edit-articles')->first(), app(Permission::class)::where('name', 'edit-news')->first()];

        $this->testUserRole->givePermissionTo($models);

        $this->assertEquals(2, $this->testUserRole->permissions()->count());

        $this->testUserRole->revokePermissionTo($models);

        $this->assertEquals(0, $this->testUserRole->permissions()->count());
    }

    /** @test */
    #[Test]
    public function it_can_give_and_revoke_permissions_models_collection()
    {
        $models = app(Permission::class)::whereIn('name', ['edit-articles', 'edit-news'])->get();

        $this->testUserRole->givePermissionTo($models);

        $this->assertEquals(2, $this->testUserRole->permissions()->count());

        $this->testUserRole->revokePermissionTo($models);

        $this->assertEquals(0, $this->testUserRole->permissions()->count());
    }

    /** @test */
    #[Test]
    public function it_can_determine_that_the_user_does_not_have_a_permission()
    {
        $this->assertFalse($this->testUser->hasPermissionTo('edit-articles'));
    }

    /** @test */
    #[Test]
    public function it_throws_an_exception_when_the_permission_does_not_exist()
    {
        $this->expectException(PermissionDoesNotExist::class);

        $this->testUser->hasPermissionTo('does-not-exist');
    }

    /** @test */
    #[Test]
    public function it_throws_an_exception_when_the_permission_does_not_exist_for_this_guard()
    {
        $this->expectException(PermissionDoesNotExist::class);

        $this->testUser->hasPermissionTo('does-not-exist', 'web');
    }

    /** @test */
    #[Test]
    public function it_can_reject_a_user_that_does_not_have_any_permissions_at_all()
    {
        $user = new User;

        $this->assertFalse($user->hasPermissionTo('edit-articles'));
    }

    /** @test */
    #[Test]
    public function it_can_determine_that_the_user_has_any_of_the_permissions_directly()
    {
        $this->assertFalse($this->testUser->hasAnyPermission('edit-articles'));

        $this->testUser->givePermissionTo('edit-articles');

        $this->assertTrue($this->testUser->hasAnyPermission('edit-news', 'edit-articles'));

        $this->testUser->givePermissionTo('edit-news');

        $this->testUser->revokePermissionTo($this->testUserPermission);

        $this->assertTrue($this->testUser->hasAnyPermission('edit-articles', 'edit-news'));
        $this->assertFalse($this->testUser->hasAnyPermission('edit-blog', 'Edit News', ['Edit News']));
    }

    /** @test */
    #[Test]
    public function it_can_determine_that_the_user_has_any_of_the_permissions_directly_using_an_array()
    {
        $this->assertFalse($this->testUser->hasAnyPermission(['edit-articles']));

        $this->testUser->givePermissionTo('edit-articles');

        $this->assertTrue($this->testUser->hasAnyPermission(['edit-news', 'edit-articles']));

        $this->testUser->givePermissionTo('edit-news');

        $this->testUser->revokePermissionTo($this->testUserPermission);

        $this->assertTrue($this->testUser->hasAnyPermission(['edit-articles', 'edit-news']));
    }

    /** @test */
    #[Test]
    public function it_can_determine_that_the_user_has_any_of_the_permissions_via_role()
    {
        $this->testUserRole->givePermissionTo('edit-articles');

        $this->testUser->assignRole('testRole');

        $this->assertTrue($this->testUser->hasAnyPermission('edit-news', 'edit-articles'));
        $this->assertFalse($this->testUser->hasAnyPermission('edit-blog', 'Edit News', ['Edit News']));
    }

    /** @test */
    #[Test]
    public function it_can_determine_that_the_user_has_all_of_the_permissions_directly()
    {
        $this->testUser->givePermissionTo('edit-articles', 'edit-news');

        $this->assertTrue($this->testUser->hasAllPermissions('edit-articles', 'edit-news'));

        $this->testUser->revokePermissionTo('edit-articles');

        $this->assertFalse($this->testUser->hasAllPermissions('edit-articles', 'edit-news'));
        $this->assertFalse($this->testUser->hasAllPermissions(['edit-articles', 'edit-news'], 'edit-blog'));
    }

    /** @test */
    #[Test]
    public function it_can_determine_that_the_user_has_all_of_the_permissions_directly_using_an_array()
    {
        $this->assertFalse($this->testUser->hasAllPermissions(['edit-articles', 'edit-news']));

        $this->testUser->revokePermissionTo('edit-articles');

        $this->assertFalse($this->testUser->hasAllPermissions(['edit-news', 'edit-articles']));

        $this->testUser->givePermissionTo('edit-news');

        $this->testUser->revokePermissionTo($this->testUserPermission);

        $this->assertFalse($this->testUser->hasAllPermissions(['edit-articles', 'edit-news']));
    }

    /** @test */
    #[Test]
    public function it_can_determine_that_the_user_has_all_of_the_permissions_via_role()
    {
        $this->testUserRole->givePermissionTo('edit-articles', 'edit-news');

        $this->testUser->assignRole('testRole');

        $this->assertTrue($this->testUser->hasAllPermissions('edit-articles', 'edit-news'));
    }

    /** @test */
    #[Test]
    public function it_can_determine_that_user_has_direct_permission()
    {
        $this->testUser->givePermissionTo('edit-articles');
        $this->assertTrue($this->testUser->hasDirectPermission('edit-articles'));
        $this->assertEquals(
            collect(['edit-articles']),
            $this->testUser->getDirectPermissions()->pluck('name')
        );

        $this->testUser->revokePermissionTo('edit-articles');
        $this->assertFalse($this->testUser->hasDirectPermission('edit-articles'));

        $this->testUser->assignRole('testRole');
        $this->testUserRole->givePermissionTo('edit-articles');
        $this->assertFalse($this->testUser->hasDirectPermission('edit-articles'));
    }

    /** @test */
    #[Test]
    public function it_can_list_all_the_permissions_via_roles_of_user()
    {
        $roleModel = app(Role::class);
        $roleModel->findByName('testRole2')->givePermissionTo('edit-news');

        $this->testUserRole->givePermissionTo('edit-articles');
        $this->testUser->assignRole('testRole', 'testRole2');

        $this->assertEquals(
            collect(['edit-articles', 'edit-news']),
            $this->testUser->getPermissionsViaRoles()->pluck('name')->sort()->values()
        );
    }

    /** @test */
    #[Test]
    public function it_can_list_all_the_coupled_permissions_both_directly_and_via_roles()
    {
        $this->testUser->givePermissionTo('edit-news');

        $this->testUserRole->givePermissionTo('edit-articles');
        $this->testUser->assignRole('testRole');

        $this->assertEquals(
            collect(['edit-articles', 'edit-news']),
            $this->testUser->getAllPermissions()->pluck('name')->sort()->values()
        );
    }

    /** @test */
    #[Test]
    public function it_can_sync_multiple_permissions()
    {
        $this->testUser->givePermissionTo('edit-news');

        $this->testUser->syncPermissions('edit-articles', 'edit-blog');

        $this->assertTrue($this->testUser->hasDirectPermission('edit-articles'));

        $this->assertTrue($this->testUser->hasDirectPermission('edit-blog'));

        $this->assertFalse($this->testUser->hasDirectPermission('edit-news'));
    }

    /** @test */
    #[Test]
    public function it_can_avoid_sync_duplicated_permissions()
    {
        $this->testUser->syncPermissions('edit-articles', 'edit-blog', 'edit-blog');

        $this->assertTrue($this->testUser->hasDirectPermission('edit-articles'));

        $this->assertTrue($this->testUser->hasDirectPermission('edit-blog'));
    }

    /** @test */
    #[Test]
    public function it_can_sync_multiple_permissions_by_id()
    {
        $this->testUser->givePermissionTo('edit-news');

        $ids = app(Permission::class)::whereIn('name', ['edit-articles', 'edit-blog'])->pluck($this->testUserPermission->getKeyName());

        $this->testUser->syncPermissions($ids);

        $this->assertTrue($this->testUser->hasDirectPermission('edit-articles'));

        $this->assertTrue($this->testUser->hasDirectPermission('edit-blog'));

        $this->assertFalse($this->testUser->hasDirectPermission('edit-news'));
    }

    /** @test */
    #[Test]
    public function sync_permission_ignores_null_inputs()
    {
        $this->testUser->givePermissionTo('edit-news');

        $ids = app(Permission::class)::whereIn('name', ['edit-articles', 'edit-blog'])->pluck($this->testUserPermission->getKeyName());

        $ids->push(null);

        $this->testUser->syncPermissions($ids);

        $this->assertTrue($this->testUser->hasDirectPermission('edit-articles'));

        $this->assertTrue($this->testUser->hasDirectPermission('edit-blog'));

        $this->assertFalse($this->testUser->hasDirectPermission('edit-news'));
    }

    /** @test */
    #[Test]
    public function sync_permission_error_does_not_detach_permissions()
    {
        $this->testUser->givePermissionTo('edit-news');

        $this->expectException(PermissionDoesNotExist::class);

        $this->testUser->syncPermissions('edit-articles', 'permission-that-does-not-exist');

        $this->assertTrue($this->testUser->fresh()->hasDirectPermission('edit-news'));
    }

    /** @test */
    #[Test]
    public function it_does_not_remove_already_associated_permissions_when_assigning_new_permissions()
    {
        $this->testUser->givePermissionTo('edit-news');

        $this->testUser->givePermissionTo('edit-articles');

        $this->assertTrue($this->testUser->fresh()->hasDirectPermission('edit-news'));
    }

    /** @test */
    #[Test]
    public function it_does_not_throw_an_exception_when_assigning_a_permission_that_is_already_assigned()
    {
        $this->testUser->givePermissionTo('edit-news');

        $this->testUser->givePermissionTo('edit-news');

        $this->assertTrue($this->testUser->fresh()->hasDirectPermission('edit-news'));
    }

    /** @test */
    #[Test]
    public function it_can_sync_permissions_to_a_model_that_is_not_persisted()
    {
        $user = new User(['email' => 'test@user.com']);
        $user->syncPermissions('edit-articles');
        $user->save();
        $user->save(); // test save same model twice

        $this->assertTrue($user->hasPermissionTo('edit-articles'));

        $user->syncPermissions('edit-articles');
        $this->assertTrue($user->hasPermissionTo('edit-articles'));
        $this->assertTrue($user->fresh()->hasPermissionTo('edit-articles'));
    }

    /** @test */
    #[Test]
    public function it_does_not_run_unnecessary_sqls_when_assigning_new_permissions()
    {
        $permission2 = app(Permission::class)->where('name', ['edit-news'])->first();

        DB::enableQueryLog();
        $this->testUser->syncPermissions($this->testUserPermission, $permission2);
        DB::disableQueryLog();

        $necessaryQueriesCount = 2;

        $this->assertCount($necessaryQueriesCount, DB::getQueryLog());
    }

    /** @test */
    #[Test]
    public function calling_givePermissionTo_before_saving_object_doesnt_interfere_with_other_objects()
    {
        $user = new User(['email' => 'test@user.com']);
        $user->givePermissionTo('edit-news');
        $user->save();

        $user2 = new User(['email' => 'test2@user.com']);
        $user2->givePermissionTo('edit-articles');

        DB::enableQueryLog();
        $user2->save();
        DB::disableQueryLog();

        $this->assertTrue($user->fresh()->hasPermissionTo('edit-news'));
        $this->assertFalse($user->fresh()->hasPermissionTo('edit-articles'));

        $this->assertTrue($user2->fresh()->hasPermissionTo('edit-articles'));
        $this->assertFalse($user2->fresh()->hasPermissionTo('edit-news'));
        $this->assertSame(2, count(DB::getQueryLog())); // avoid unnecessary sync
    }

    /** @test */
    #[Test]
    public function calling_syncPermissions_before_saving_object_doesnt_interfere_with_other_objects()
    {
        $user = new User(['email' => 'test@user.com']);
        $user->syncPermissions('edit-news');
        $user->save();

        $user2 = new User(['email' => 'test2@user.com']);
        $user2->syncPermissions('edit-articles');

        DB::enableQueryLog();
        $user2->save();
        DB::disableQueryLog();

        $this->assertTrue($user->fresh()->hasPermissionTo('edit-news'));
        $this->assertFalse($user->fresh()->hasPermissionTo('edit-articles'));

        $this->assertTrue($user2->fresh()->hasPermissionTo('edit-articles'));
        $this->assertFalse($user2->fresh()->hasPermissionTo('edit-news'));
        $this->assertSame(2, count(DB::getQueryLog())); // avoid unnecessary sync
    }

    /** @test */
    #[Test]
    public function it_can_retrieve_permission_names()
    {
        $this->testUser->givePermissionTo('edit-news', 'edit-articles');
        $this->assertEquals(
            collect(['edit-articles', 'edit-news']),
            $this->testUser->getPermissionNames()->sort()->values()
        );
    }

    /** @test */
    #[Test]
    public function it_can_check_many_direct_permissions()
    {
        $this->testUser->givePermissionTo(['edit-articles', 'edit-news']);
        $this->assertTrue($this->testUser->hasAllDirectPermissions(['edit-news', 'edit-articles']));
        $this->assertTrue($this->testUser->hasAllDirectPermissions('edit-news', 'edit-articles'));
        $this->assertFalse($this->testUser->hasAllDirectPermissions(['edit-articles', 'edit-news', 'edit-blog']));
        $this->assertFalse($this->testUser->hasAllDirectPermissions(['edit-articles', 'edit-news'], 'edit-blog'));
    }

    /** @test */
    #[Test]
    public function it_can_check_if_there_is_any_of_the_direct_permissions_given()
    {
        $this->testUser->givePermissionTo(['edit-articles', 'edit-news']);
        $this->assertTrue($this->testUser->hasAnyDirectPermission(['edit-news', 'edit-blog']));
        $this->assertTrue($this->testUser->hasAnyDirectPermission('edit-news', 'edit-blog'));
        $this->assertFalse($this->testUser->hasAnyDirectPermission('edit-blog', 'Edit News', ['Edit News']));
    }

    /** @test */
    #[Test]
    public function it_can_check_permission_based_on_logged_in_user_guard()
    {
        $this->testUser->givePermissionTo(app(Permission::class)::create([
            'name' => 'do_that',
            'guard_name' => 'api',
        ]));
        $response = $this->actingAs($this->testUser, 'api')
            ->json('GET', '/check-api-guard-permission');
        $response->assertJson([
            'status' => true,
        ]);
    }

    /** @test */
    #[Test]
    public function it_can_reject_permission_based_on_logged_in_user_guard()
    {
        $unassignedPermission = app(Permission::class)::create([
            'name' => 'do_that',
            'guard_name' => 'api',
        ]);

        $assignedPermission = app(Permission::class)::create([
            'name' => 'do_that',
            'guard_name' => 'web',
        ]);

        $this->testUser->givePermissionTo($assignedPermission);
        $response = $this->withExceptionHandling()
            ->actingAs($this->testUser, 'api')
            ->json('GET', '/check-api-guard-permission');
        $response->assertJson([
            'status' => false,
        ]);
    }

    /** @test */
    #[Test]
    public function it_fires_an_event_when_a_permission_is_added()
    {
        Event::fake();
        app('config')->set('permission.events_enabled', true);

        $this->testUser->givePermissionTo(['edit-articles', 'edit-news']);

        $ids = app(Permission::class)::whereIn('name', ['edit-articles', 'edit-news'])
            ->pluck($this->testUserPermission->getKeyName())
            ->toArray();

        Event::assertDispatched(PermissionAttached::class, function ($event) use ($ids) {
            return $event->model instanceof User
                && $event->model->hasPermissionTo('edit-news')
                && $event->model->hasPermissionTo('edit-articles')
                && $ids === $event->permissionsOrIds;
        });
    }

    /** @test */
    #[Test]
    public function it_does_not_fire_an_event_when_events_are_not_enabled()
    {
        Event::fake();
        app('config')->set('permission.events_enabled', false);

        $this->testUser->givePermissionTo(['edit-articles', 'edit-news']);

        $ids = app(Permission::class)::whereIn('name', ['edit-articles', 'edit-news'])
            ->pluck($this->testUserPermission->getKeyName())
            ->toArray();

        Event::assertNotDispatched(PermissionAttached::class);
    }

    /** @test */
    #[Test]
    public function it_fires_an_event_when_a_permission_is_removed()
    {
        Event::fake();
        app('config')->set('permission.events_enabled', true);

        $permissions = app(Permission::class)::whereIn('name', ['edit-articles', 'edit-news'])->get();

        $this->testUser->givePermissionTo($permissions);

        $this->testUser->revokePermissionTo($permissions);

        Event::assertDispatched(PermissionDetached::class, function ($event) use ($permissions) {
            return $event->model instanceof User
                && ! $event->model->hasPermissionTo('edit-news')
                && ! $event->model->hasPermissionTo('edit-articles')
                && $event->permissionsOrIds === $permissions;
        });
    }

    /** @test */
    #[Test]
    public function it_can_be_given_a_permission_on_role_when_lazy_loading_is_restricted()
    {
        $this->assertTrue(Model::preventsLazyLoading());

        try {
            $testRole = app(Role::class)->with('permissions')->get()->first();

            $testRole->givePermissionTo('edit-articles');

            $this->assertTrue($testRole->hasPermissionTo('edit-articles'));
        } catch (Exception $e) {
            $this->fail('Lazy loading detected in the givePermissionTo method: '.$e->getMessage());
        }
    }

    /** @test */
    #[Test]
    public function it_can_be_given_a_permission_on_user_when_lazy_loading_is_restricted()
    {
        $this->assertTrue(Model::preventsLazyLoading());

        try {
            User::create(['email' => 'other@user.com']);
            $testUser = User::with('permissions')->get()->first();

            $testUser->givePermissionTo('edit-articles');

            $this->assertTrue($testUser->hasPermissionTo('edit-articles'));
        } catch (Exception $e) {
            $this->fail('Lazy loading detected in the givePermissionTo method: '.$e->getMessage());
        }
    }
}
