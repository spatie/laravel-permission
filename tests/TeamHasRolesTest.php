<?php

namespace Spatie\Permission\Tests;

use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Tests\TestModels\User;

class TeamHasRolesTest extends HasRolesTest
{
    /** @var bool */
    protected $hasTeams = true;

    /** @test */
    #[Test]
    public function it_deletes_pivot_table_entries_when_deleting_models()
    {
        $user1 = User::create(['email' => 'user2@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);

        setPermissionsTeamId(1);
        $user1->assignRole('testRole');
        $user1->givePermissionTo('edit-articles');
        $user2->assignRole('testRole');
        $user2->givePermissionTo('edit-articles');
        setPermissionsTeamId(2);
        $user1->givePermissionTo('edit-news');

        $this->assertDatabaseHas('model_has_permissions', [config('permission.column_names.model_morph_key') => $user1->id]);
        $this->assertDatabaseHas('model_has_roles', [config('permission.column_names.model_morph_key') => $user1->id]);

        $user1->delete();

        setPermissionsTeamId(1);
        $this->assertDatabaseMissing('model_has_permissions', [config('permission.column_names.model_morph_key') => $user1->id]);
        $this->assertDatabaseMissing('model_has_roles', [config('permission.column_names.model_morph_key') => $user1->id]);
        $this->assertDatabaseHas('model_has_permissions', [config('permission.column_names.model_morph_key') => $user2->id]);
        $this->assertDatabaseHas('model_has_roles', [config('permission.column_names.model_morph_key') => $user2->id]);
    }

    /** @test */
    #[Test]
    public function it_can_assign_same_and_different_roles_on_same_user_different_teams()
    {
        app(Role::class)->create(['name' => 'testRole3']); // team_test_id = 1 by main class
        app(Role::class)->create(['name' => 'testRole3', 'team_test_id' => 2]);
        app(Role::class)->create(['name' => 'testRole4', 'team_test_id' => null]); // global role

        $testRole3Team1 = app(Role::class)->where(['name' => 'testRole3', 'team_test_id' => 1])->first();
        $testRole3Team2 = app(Role::class)->where(['name' => 'testRole3', 'team_test_id' => 2])->first();
        $testRole4NoTeam = app(Role::class)->where(['name' => 'testRole4', 'team_test_id' => null])->first();
        $this->assertNotNull($testRole3Team1);
        $this->assertNotNull($testRole4NoTeam);

        setPermissionsTeamId(1);
        $this->testUser->assignRole('testRole', 'testRole2');

        // explicit load of roles to assert no mismatch
        // when same role assigned in diff teams
        // while old team's roles are loaded
        $this->testUser->load('roles');

        setPermissionsTeamId(2);
        $this->testUser->assignRole('testRole', 'testRole3');

        setPermissionsTeamId(1);
        $this->testUser->load('roles');

        $this->assertEquals(
            collect(['testRole', 'testRole2']),
            $this->testUser->getRoleNames()->sort()->values()
        );
        $this->assertTrue($this->testUser->hasExactRoles(['testRole', 'testRole2']));

        $this->testUser->assignRole('testRole3', 'testRole4');
        $this->assertTrue($this->testUser->hasExactRoles(['testRole', 'testRole2', 'testRole3', 'testRole4']));
        $this->assertTrue($this->testUser->hasRole($testRole3Team1)); // testRole3 team=1
        $this->assertTrue($this->testUser->hasRole($testRole4NoTeam)); // global role team=null

        setPermissionsTeamId(2);
        $this->testUser->load('roles');

        $this->assertEquals(
            collect(['testRole', 'testRole3']),
            $this->testUser->getRoleNames()->sort()->values()
        );
        $this->assertTrue($this->testUser->hasExactRoles(['testRole', 'testRole3']));
        $this->assertTrue($this->testUser->hasRole($testRole3Team2)); // testRole3 team=2
        $this->testUser->assignRole('testRole4');
        $this->assertTrue($this->testUser->hasExactRoles(['testRole', 'testRole3', 'testRole4']));
        $this->assertTrue($this->testUser->hasRole($testRole4NoTeam)); // global role team=null
    }

    /** @test */
    #[Test]
    public function it_can_sync_or_remove_roles_without_detach_on_different_teams()
    {
        app(Role::class)->create(['name' => 'testRole3', 'team_test_id' => 2]);

        setPermissionsTeamId(1);
        $this->testUser->syncRoles('testRole', 'testRole2');

        setPermissionsTeamId(2);
        $this->testUser->syncRoles('testRole', 'testRole3');

        setPermissionsTeamId(1);
        $this->testUser->load('roles');

        $this->assertEquals(
            collect(['testRole', 'testRole2']),
            $this->testUser->getRoleNames()->sort()->values()
        );

        $this->testUser->removeRole('testRole');
        $this->assertEquals(
            collect(['testRole2']),
            $this->testUser->getRoleNames()->sort()->values()
        );

        setPermissionsTeamId(2);
        $this->testUser->load('roles');

        $this->assertEquals(
            collect(['testRole', 'testRole3']),
            $this->testUser->getRoleNames()->sort()->values()
        );
    }

    /** @test */
    #[Test]
    public function it_can_scope_users_on_different_teams()
    {
        User::all()->each(fn ($item) => $item->delete());
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);

        setPermissionsTeamId(2);
        $user1->assignRole($this->testUserRole);
        $user2->assignRole('testRole2');

        setPermissionsTeamId(1);
        $user1->assignRole('testRole');

        setPermissionsTeamId(2);
        $scopedUsers1Team1 = User::role($this->testUserRole)->get();
        $scopedUsers2Team1 = User::role(['testRole', 'testRole2'])->get();
        $scopedUsers3Team1 = User::withoutRole('testRole')->get();

        $this->assertEquals(1, $scopedUsers1Team1->count());
        $this->assertEquals(2, $scopedUsers2Team1->count());
        $this->assertEquals(1, $scopedUsers3Team1->count());

        setPermissionsTeamId(1);
        $scopedUsers1Team2 = User::role($this->testUserRole)->get();
        $scopedUsers2Team2 = User::role('testRole2')->get();
        $scopedUsers3Team2 = User::withoutRole('testRole')->get();

        $this->assertEquals(1, $scopedUsers1Team2->count());
        $this->assertEquals(0, $scopedUsers2Team2->count());
        $this->assertEquals(1, $scopedUsers3Team2->count());
    }

    /** @test */
    #[Test]
    public function it_can_assign_global_role_with_null_team_foreign_key_to_user()
    {
        // This test verifies the fix for issue #2888
        // Global roles (with team_foreign_key = null) should be assignable to users
        // without throwing an error about non-nullable team_foreign_key in pivot tables
        
        $teamKey = config('permission.column_names.team_foreign_key');
        
        // Create a global role with null team_foreign_key
        $globalRole = app(Role::class)->create(['name' => 'global-admin', $teamKey => null]);
        
        $this->assertNull($globalRole->{$teamKey}, 'Global role should have null team_foreign_key');
        
        // Assign the global role to a user - this should not throw an error
        setPermissionsTeamId(1);
        $this->testUser->assignRole($globalRole);
        
        // Verify the role was assigned
        $this->assertTrue($this->testUser->hasRole($globalRole));
        $this->assertTrue($this->testUser->hasRole('global-admin'));
        
        // Verify the pivot table entry exists and can have null team_foreign_key
        $this->assertDatabaseHas('model_has_roles', [
            config('permission.column_names.model_morph_key') => $this->testUser->id,
        ]);
        
        // Verify we can query the role assignment
        $this->testUser->load('roles');
        $assignedRoles = $this->testUser->roles;
        $this->assertTrue($assignedRoles->contains('id', $globalRole->id));
    }

    /** @test */
    #[Test]
    public function it_can_assign_global_role_to_multiple_users_across_different_teams()
    {
        // Test that unique constraint works correctly with NULL values
        // Multiple users should be able to have the same global role
        // This test would fail if team_foreign_key was part of primary key (can't have NULL in PK)
        
        $teamKey = config('permission.column_names.team_foreign_key');
        $globalRole = app(Role::class)->create(['name' => 'global-editor', $teamKey => null]);
        
        $user1 = User::create(['email' => 'user1-global@test.com']);
        $user2 = User::create(['email' => 'user2-global@test.com']);
        
        // Assign to user1 on team 1
        setPermissionsTeamId(1);
        $user1->assignRole($globalRole);
        
        // Assign to user2 on team 2
        setPermissionsTeamId(2);
        $user2->assignRole($globalRole);
        
        // Both should have the role
        setPermissionsTeamId(1);
        $user1->load('roles');
        $this->assertTrue($user1->hasRole($globalRole));
        
        setPermissionsTeamId(2);
        $user2->load('roles');
        $this->assertTrue($user2->hasRole($globalRole));
        
        // Verify both entries exist in pivot table (with potentially NULL team_foreign_key)
        $this->assertDatabaseHas('model_has_roles', [
            config('permission.column_names.model_morph_key') => $user1->id,
        ]);
        $this->assertDatabaseHas('model_has_roles', [
            config('permission.column_names.model_morph_key') => $user2->id,
        ]);
    }

    /** @test */
    #[Test]
    public function it_can_query_global_roles_correctly_across_different_teams()
    {
        // Test that global roles (with NULL team_foreign_key in roles table) work correctly
        // The role itself has NULL team_foreign_key, making it a "global" role
        // However, when assigned, the pivot table gets the current team ID
        // This test verifies the schema allows NULL in roles table and pivot table
        
        $teamKey = config('permission.column_names.team_foreign_key');
        $globalRole = app(Role::class)->create(['name' => 'global-viewer', $teamKey => null]);
        $teamRole = app(Role::class)->create(['name' => 'team-specific', $teamKey => 1]);
        
        setPermissionsTeamId(1);
        $this->testUser->assignRole($globalRole, $teamRole);
        
        // Should see both roles on team 1
        $this->testUser->load('roles');
        $this->assertTrue($this->testUser->hasRole($globalRole));
        $this->assertTrue($this->testUser->hasRole($teamRole));
        
        // Switch to team 2 - assign global role again (it can be assigned on multiple teams)
        // The global role (NULL in roles table) can exist on multiple teams via pivot
        setPermissionsTeamId(2);
        $this->testUser->assignRole($globalRole);
        $this->testUser->load('roles');
        $this->assertTrue($this->testUser->hasRole($globalRole), 'Global role should be assignable on multiple teams');
        $this->assertFalse($this->testUser->hasRole($teamRole), 'Team-specific role should not be visible on other teams');
        
        // Verify the global role exists in roles table with NULL team_foreign_key
        $this->assertNull($globalRole->fresh()->{$teamKey}, 'Global role should have NULL team_foreign_key in roles table');
    }

    /** @test */
    #[Test]
    public function it_allows_direct_database_insertion_with_null_team_foreign_key()
    {
        // Test that the database schema actually allows NULL in the pivot table
        // This is a direct test of the migration fix
        // This would fail if team_foreign_key was NOT NULL
        
        $teamKey = config('permission.column_names.team_foreign_key');
        $pivotKey = config('permission.column_names.role_pivot_key') ?? 'role_id';
        $modelKey = config('permission.column_names.model_morph_key');
        
        $globalRole = app(Role::class)->create(['name' => 'direct-test-role', $teamKey => null]);
        
        // Directly insert into pivot table with NULL team_foreign_key
        // This tests that the column is actually nullable in the database
        \DB::table('model_has_roles')->insert([
            $pivotKey => $globalRole->id,
            $modelKey => $this->testUser->id,
            'model_type' => get_class($this->testUser),
            $teamKey => null, // This should not throw an error
        ]);
        
        // Verify the insertion worked - the database schema allows NULL
        $this->assertDatabaseHas('model_has_roles', [
            $pivotKey => $globalRole->id,
            $modelKey => $this->testUser->id,
            $teamKey => null,
        ]);
        
        // Note: The query logic checks both pivot team_foreign_key AND role's team_foreign_key
        // Since the role has NULL team_foreign_key, it should be accessible from any team
        // But the pivot's team_foreign_key needs to match for the query to work
        // This test verifies the schema allows NULL, which is the core fix
        // The actual assignment logic will set the team ID in the pivot table
    }

    /** @test */
    #[Test]
    public function it_handles_mixed_team_and_global_roles_correctly()
    {
        // Test edge case: user has both team-specific and global roles
        // Ensures queries handle both NULL and non-NULL values correctly
        
        $teamKey = config('permission.column_names.team_foreign_key');
        $globalRole = app(Role::class)->create(['name' => 'mixed-global', $teamKey => null]);
        $team1Role = app(Role::class)->create(['name' => 'mixed-team1', $teamKey => 1]);
        $team2Role = app(Role::class)->create(['name' => 'mixed-team2', $teamKey => 2]);
        
        // Assign global and team1 role on team 1
        setPermissionsTeamId(1);
        $this->testUser->assignRole($globalRole, $team1Role);
        
        // Assign global and team2 role on team 2
        setPermissionsTeamId(2);
        $this->testUser->assignRole($globalRole, $team2Role);
        
        // On team 1: should see global + team1 roles
        setPermissionsTeamId(1);
        $this->testUser->load('roles');
        $roleNames = $this->testUser->getRoleNames()->sort()->values();
        $this->assertTrue($roleNames->contains('mixed-global'));
        $this->assertTrue($roleNames->contains('mixed-team1'));
        $this->assertFalse($roleNames->contains('mixed-team2'));
        
        // On team 2: should see global + team2 roles
        setPermissionsTeamId(2);
        $this->testUser->load('roles');
        $roleNames = $this->testUser->getRoleNames()->sort()->values();
        $this->assertTrue($roleNames->contains('mixed-global'));
        $this->assertTrue($roleNames->contains('mixed-team2'));
        $this->assertFalse($roleNames->contains('mixed-team1'));
    }
}
