<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Exceptions\WildcardPermissionInvalidArgument;
use Spatie\Permission\Exceptions\WildcardPermissionNotProperlyFormatted;

class WildcardHasPermissionsTest extends TestCase
{
    /** @test */
    public function it_can_check_wildcard_permission()
    {
        app('config')->set('permission.enable_wildcard_permission', true);

        $user1 = User::create(['email' => 'user1@test.com']);

        $permission1 = Permission::create(['name' => 'articles.edit,view,create']);
        $permission2 = Permission::create(['name' => 'news.*']);
        $permission3 = Permission::create(['name' => 'posts.*']);

        $user1->givePermissionTo([$permission1, $permission2, $permission3]);

        $this->assertTrue($user1->hasPermissionTo('posts.create'));
        $this->assertTrue($user1->hasPermissionTo('posts.create.123'));
        $this->assertTrue($user1->hasPermissionTo('posts.*'));
        $this->assertTrue($user1->hasPermissionTo('articles.view'));
        $this->assertFalse($user1->hasPermissionTo('projects.view'));
    }

    /** @test */
    public function it_can_check_wildcard_permissions_via_roles()
    {
        app('config')->set('permission.enable_wildcard_permission', true);

        $user1 = User::create(['email' => 'user1@test.com']);

        $user1->assignRole('testRole');

        $permission1 = Permission::create(['name' => 'articles,projects.edit,view,create']);
        $permission2 = Permission::create(['name' => 'news.*.456']);
        $permission3 = Permission::create(['name' => 'posts']);

        $this->testUserRole->givePermissionTo([$permission1, $permission2, $permission3]);

        $this->assertTrue($user1->hasPermissionTo('posts.create'));
        $this->assertTrue($user1->hasPermissionTo('news.create.456'));
        $this->assertTrue($user1->hasPermissionTo('projects.create'));
        $this->assertTrue($user1->hasPermissionTo('articles.view'));
        $this->assertFalse($user1->hasPermissionTo('articles.list'));
        $this->assertFalse($user1->hasPermissionTo('projects.list'));
    }

    /** @test */
    public function it_can_check_non_wildcard_permissions()
    {
        app('config')->set('permission.enable_wildcard_permission', true);

        $user1 = User::create(['email' => 'user1@test.com']);

        $permission1 = Permission::create(['name' => 'edit articles']);
        $permission2 = Permission::create(['name' => 'create news']);
        $permission3 = Permission::create(['name' => 'update comments']);

        $user1->givePermissionTo([$permission1, $permission2, $permission3]);

        $this->assertTrue($user1->hasPermissionTo('edit articles'));
        $this->assertTrue($user1->hasPermissionTo('create news'));
        $this->assertTrue($user1->hasPermissionTo('update comments'));
    }

    /** @test */
    public function it_can_verify_complex_wildcard_permissions()
    {
        app('config')->set('permission.enable_wildcard_permission', true);

        $user1 = User::create(['email' => 'user1@test.com']);

        $permission1 = Permission::create(['name' => '*.create,update,delete.*.test,course,finance']);
        $permission2 = Permission::create(['name' => 'papers,posts,projects,orders.*.test,test1,test2.*']);
        $permission3 = Permission::create(['name' => 'User::class.create,edit,view']);

        $user1->givePermissionTo([$permission1, $permission2, $permission3]);

        $this->assertTrue($user1->hasPermissionTo('invoices.delete.367463.finance'));
        $this->assertTrue($user1->hasPermissionTo('projects.update.test2.test3'));
        $this->assertTrue($user1->hasPermissionTo('User::class.edit'));
        $this->assertFalse($user1->hasPermissionTo('User::class.delete'));
        $this->assertFalse($user1->hasPermissionTo('User::class.*'));
    }

    /** @test */
    public function it_throws_exception_when_wildcard_permission_is_not_properly_formatted()
    {
        app('config')->set('permission.enable_wildcard_permission', true);

        $user1 = User::create(['email' => 'user1@test.com']);

        $permission = Permission::create(['name' => '*..']);

        $user1->givePermissionTo([$permission]);

        $this->expectException(WildcardPermissionNotProperlyFormatted::class);

        $user1->hasPermissionTo('invoices.*');
    }

    /** @test */
    public function it_can_verify_permission_instances_not_assigned_to_user()
    {
        app('config')->set('permission.enable_wildcard_permission', true);

        $user = User::create(['email' => 'user@test.com']);

        $userPermission = Permission::create(['name' => 'posts.*']);
        $permissionToVerify = Permission::create(['name' => 'posts.create']);

        $user->givePermissionTo([$userPermission]);

        $this->assertTrue($user->hasPermissionTo('posts.create'));
        $this->assertTrue($user->hasPermissionTo('posts.create.123'));
        $this->assertTrue($user->hasPermissionTo($permissionToVerify->id));
        $this->assertTrue($user->hasPermissionTo($permissionToVerify));
    }

    /** @test */
    public function it_can_verify_permission_instances_assigned_to_user()
    {
        app('config')->set('permission.enable_wildcard_permission', true);

        $user = User::create(['email' => 'user@test.com']);

        $userPermission = Permission::create(['name' => 'posts.*']);
        $permissionToVerify = Permission::create(['name' => 'posts.create']);

        $user->givePermissionTo([$userPermission, $permissionToVerify]);

        $this->assertTrue($user->hasPermissionTo('posts.create'));
        $this->assertTrue($user->hasPermissionTo('posts.create.123'));
        $this->assertTrue($user->hasPermissionTo($permissionToVerify));
        $this->assertTrue($user->hasPermissionTo($userPermission));
    }

    /** @test */
    public function it_can_verify_integers_as_strings()
    {
        app('config')->set('permission.enable_wildcard_permission', true);

        $user = User::create(['email' => 'user@test.com']);

        $userPermission = Permission::create(['name' => '8']);

        $user->givePermissionTo([$userPermission]);

        $this->assertTrue($user->hasPermissionTo('8'));
    }

    /** @test */
    public function it_throws_exception_when_permission_has_invalid_arguments()
    {
        app('config')->set('permission.enable_wildcard_permission', true);

        $user = User::create(['email' => 'user@test.com']);

        $this->expectException(WildcardPermissionInvalidArgument::class);

        $user->hasPermissionTo(['posts.create']);
    }

    /** @test */
    public function it_throws_exception_when_permission_id_not_exists()
    {
        app('config')->set('permission.enable_wildcard_permission', true);

        $user = User::create(['email' => 'user@test.com']);

        $this->expectException(PermissionDoesNotExist::class);

        $user->hasPermissionTo(6);
    }
}
