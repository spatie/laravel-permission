<?php

namespace Spatie\Permission\Tests;

use Spatie\Permission\Tests\TestModels\User;

class TeamHasPermissionsTest extends HasPermissionsTest
{
    /** @var bool */
    protected $hasTeams = true;

    /** @test */
    public function it_can_assign_same_and_different_permission_on_same_user_on_different_teams()
    {
        setPermissionsTeamId(1);
        $this->testUser->givePermissionTo('edit-articles', 'edit-news');

        setPermissionsTeamId(2);
        $this->testUser->givePermissionTo('edit-articles', 'edit-blog');

        setPermissionsTeamId(1);
        $this->testUser->load('permissions');
        $this->assertEquals(
            collect(['edit-articles', 'edit-news']),
            $this->testUser->getPermissionNames()->sort()->values()
        );
        $this->assertTrue($this->testUser->hasAllDirectPermissions(['edit-articles', 'edit-news']));
        $this->assertFalse($this->testUser->hasAllDirectPermissions(['edit-articles', 'edit-blog']));

        setPermissionsTeamId(2);
        $this->testUser->load('permissions');
        $this->assertEquals(
            collect(['edit-articles', 'edit-blog']),
            $this->testUser->getPermissionNames()->sort()->values()
        );
        $this->assertTrue($this->testUser->hasAllDirectPermissions(['edit-articles', 'edit-blog']));
        $this->assertFalse($this->testUser->hasAllDirectPermissions(['edit-articles', 'edit-news']));
    }

    /** @test */
    public function it_can_list_all_the_coupled_permissions_both_directly_and_via_roles_on_same_user_on_different_teams()
    {
        $this->testUserRole->givePermissionTo('edit-articles');

        setPermissionsTeamId(1);
        $this->testUser->assignRole('testRole');
        $this->testUser->givePermissionTo('edit-news');

        setPermissionsTeamId(2);
        $this->testUser->assignRole('testRole');
        $this->testUser->givePermissionTo('edit-blog');

        setPermissionsTeamId(1);
        $this->testUser->load('roles', 'permissions');

        $this->assertEquals(
            collect(['edit-articles', 'edit-news']),
            $this->testUser->getAllPermissions()->pluck('name')->sort()->values()
        );

        setPermissionsTeamId(2);
        $this->testUser->load('roles', 'permissions');

        $this->assertEquals(
            collect(['edit-articles', 'edit-blog']),
            $this->testUser->getAllPermissions()->pluck('name')->sort()->values()
        );
    }

    /** @test */
    public function it_can_sync_or_remove_permission_without_detach_on_different_teams()
    {
        setPermissionsTeamId(1);
        $this->testUser->syncPermissions('edit-articles', 'edit-news');

        setPermissionsTeamId(2);
        $this->testUser->syncPermissions('edit-articles', 'edit-blog');

        setPermissionsTeamId(1);
        $this->testUser->load('permissions');

        $this->assertEquals(
            collect(['edit-articles', 'edit-news']),
            $this->testUser->getPermissionNames()->sort()->values()
        );

        $this->testUser->revokePermissionTo('edit-articles');
        $this->assertEquals(
            collect(['edit-news']),
            $this->testUser->getPermissionNames()->sort()->values()
        );

        setPermissionsTeamId(2);
        $this->testUser->load('permissions');
        $this->assertEquals(
            collect(['edit-articles', 'edit-blog']),
            $this->testUser->getPermissionNames()->sort()->values()
        );
    }

    /** @test */
    public function it_can_scope_users_on_different_teams()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);

        setPermissionsTeamId(2);
        $user1->givePermissionTo(['edit-articles', 'edit-news']);
        $this->testUserRole->givePermissionTo('edit-articles');
        $user2->assignRole('testRole');

        setPermissionsTeamId(1);
        $user1->givePermissionTo(['edit-articles']);

        setPermissionsTeamId(2);
        $scopedUsers1Team2 = User::permission(['edit-articles', 'edit-news'])->get();
        $scopedUsers2Team2 = User::permission('edit-news')->get();

        $this->assertEquals(2, $scopedUsers1Team2->count());
        $this->assertEquals(1, $scopedUsers2Team2->count());

        setPermissionsTeamId(1);
        $scopedUsers1Team1 = User::permission(['edit-articles', 'edit-news'])->get();
        $scopedUsers2Team1 = User::permission('edit-news')->get();

        $this->assertEquals(1, $scopedUsers1Team1->count());
        $this->assertEquals(0, $scopedUsers2Team1->count());
    }
}
