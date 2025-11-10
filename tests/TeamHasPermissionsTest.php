<?php

namespace Spatie\Permission\Tests;

use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Tests\TestModels\User;

class TeamHasPermissionsTest extends HasPermissionsTest
{
    /** @var bool */
    protected $hasTeams = true;

    /** @test */
    #[Test]
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
    #[Test]
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
    #[Test]
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
    #[Test]
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

    /** @test */
    #[Test]
    public function it_allows_direct_database_insertion_with_null_team_foreign_key_for_permissions()
    {
        // Test that the database schema actually allows NULL in model_has_permissions pivot table
        // This is a direct test of the migration fix for issue #2888
        // This would fail if team_foreign_key was NOT NULL
        
        $teamKey = config('permission.column_names.team_foreign_key');
        $pivotKey = config('permission.column_names.permission_pivot_key') ?? 'permission_id';
        $modelKey = config('permission.column_names.model_morph_key');
        
        $permission = \Spatie\Permission\Models\Permission::create(['name' => 'direct-test-permission']);
        
        // Directly insert into pivot table with NULL team_foreign_key
        // This tests that the column is actually nullable in the database
        \DB::table('model_has_permissions')->insert([
            $pivotKey => $permission->id,
            $modelKey => $this->testUser->id,
            'model_type' => get_class($this->testUser),
            $teamKey => null, // This should not throw an error
        ]);
        
        // Verify the insertion worked - the database schema allows NULL
        $this->assertDatabaseHas('model_has_permissions', [
            $pivotKey => $permission->id,
            $modelKey => $this->testUser->id,
            $teamKey => null,
        ]);
        
        // Note: The query logic checks pivot team_foreign_key against getPermissionsTeamId()
        // So NULL in pivot won't match unless team ID is also NULL
        // This test verifies the schema allows NULL, which is the core fix for issue #2888
        // The actual assignment logic (givePermissionTo) will set the team ID in the pivot table
    }
}
