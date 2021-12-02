<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Contracts\Role;

class TeamHasRolesTest extends HasRolesTest
{
    /** @var bool */
    protected $hasTeams = true;

    /** @test */
    public function it_can_scope_roles_using_user_id_and_team_id()
    {
        $user1 = User::create(['email' => 'user1@test.com']);

        setPermissionsTeamId(1);
        $user1->syncRoles(['testRole', 'testRole2']);
        setPermissionsTeamId(2);
        $user1->syncRoles('testRole');

        $scopedRoles1 = app(Role::class)::user($user1, 1)->get();
        $scopedRoles2 = app(Role::class)::user($user1, 2)->get();
        $scopedRoles3 = app(Role::class)::user($user1->id, 3)->get();

        $this->assertEquals(['testRole', 'testRole2'], $scopedRoles1->pluck('name')->toArray());
        $this->assertEquals(['testRole'], $scopedRoles2->pluck('name')->toArray());
        $this->assertEquals([], $scopedRoles3->pluck('name')->toArray());
    }

    /** @test */
    public function it_can_assign_same_and_different_roles_on_same_user_different_teams()
    {
        app(Role::class)->create(['name' => 'testRole3']); //team_test_id = 1 by main class
        app(Role::class)->create(['name' => 'testRole3', 'team_test_id' => 2]);
        app(Role::class)->create(['name' => 'testRole4', 'team_test_id' => null]); //global role

        $testRole3Team1 = app(Role::class)->where(['name' => 'testRole3', 'team_test_id' => 1])->first();
        $testRole3Team2 = app(Role::class)->where(['name' => 'testRole3', 'team_test_id' => 2])->first();
        $testRole4NoTeam = app(Role::class)->where(['name' => 'testRole4', 'team_test_id' => null])->first();
        $this->assertNotNull($testRole3Team1);
        $this->assertNotNull($testRole4NoTeam);

        $this->setPermissionsTeamId(1);
        $this->testUser->load('roles');
        $this->testUser->assignRole('testRole', 'testRole2');

        $this->setPermissionsTeamId(2);
        $this->testUser->load('roles');
        $this->testUser->assignRole('testRole', 'testRole3');

        $this->setPermissionsTeamId(1);
        $this->testUser->load('roles');

        $this->assertEquals(
            collect(['testRole', 'testRole2']),
            $this->testUser->getRoleNames()->sort()->values()
        );
        $this->assertTrue($this->testUser->hasExactRoles(['testRole', 'testRole2']));

        $this->testUser->assignRole('testRole3', 'testRole4');
        $this->assertTrue($this->testUser->hasExactRoles(['testRole', 'testRole2', 'testRole3', 'testRole4']));
        $this->assertTrue($this->testUser->hasRole($testRole3Team1)); //testRole3 team=1
        $this->assertTrue($this->testUser->hasRole($testRole4NoTeam)); // global role team=null

        $this->setPermissionsTeamId(2);
        $this->testUser->load('roles');

        $this->assertEquals(
            collect(['testRole', 'testRole3']),
            $this->testUser->getRoleNames()->sort()->values()
        );
        $this->assertTrue($this->testUser->hasExactRoles(['testRole', 'testRole3']));
        $this->assertTrue($this->testUser->hasRole($testRole3Team2)); //testRole3 team=2
        $this->testUser->assignRole('testRole4');
        $this->assertTrue($this->testUser->hasExactRoles(['testRole', 'testRole3', 'testRole4']));
        $this->assertTrue($this->testUser->hasRole($testRole4NoTeam)); // global role team=null
    }

    /** @test */
    public function it_can_sync_or_remove_roles_without_detach_on_different_teams()
    {
        app(Role::class)->create(['name' => 'testRole3', 'team_test_id' => 2]);

        $this->setPermissionsTeamId(1);
        $this->testUser->load('roles');
        $this->testUser->syncRoles('testRole', 'testRole2');

        $this->setPermissionsTeamId(2);
        $this->testUser->load('roles');
        $this->testUser->syncRoles('testRole', 'testRole3');

        $this->setPermissionsTeamId(1);
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

        $this->setPermissionsTeamId(2);
        $this->testUser->load('roles');

        $this->assertEquals(
            collect(['testRole', 'testRole3']),
            $this->testUser->getRoleNames()->sort()->values()
        );
    }
}
