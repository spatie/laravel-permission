<?php

namespace Spatie\Permission\Test;

use Illuminate\Database\Schema\Blueprint;
use Spatie\Permission\Contracts\Role;

class TeamHasRolesTraitTest extends TestCase
{
    /** @var bool */
    protected $hasTeams = true;

    /** @var \Spatie\Permission\Test\Team */
    protected $testTeam1;

    /** @var \Spatie\Permission\Test\Team */
    protected $testTeam2;

    protected function setUpDatabase($app)
    {
        parent::setUpDatabase($app);

        $app['db']->connection()->getSchemaBuilder()->create('teams', function (Blueprint $table) {
            $table->increments('id');
            $table->softDeletes();
        });
        $this->testTeam1 = Team::create([]);
        $this->testTeam2 = Team::create([]);
    }

    /** @test */
    public function it_deletes_pivot_table_entries_when_deleting_teams()
    {
        $user = User::create(['email' => 'user@test.com']);

        setPermissionsTeamId($this->testTeam1->id);
        $user->assignRole('testRole');
        $user->givePermissionTo('edit-articles');
        setPermissionsTeamId($this->testTeam2->id);
        $user->assignRole('testRole2');
        $user->givePermissionTo('edit-news');

        setPermissionsTeamId($this->testTeam1->id);
        $this->assertDatabaseHas('model_has_permissions', [config('permission.column_names.team_foreign_key') => $this->testTeam1->id]);
        $this->assertDatabaseHas('model_has_roles', [config('permission.column_names.team_foreign_key') => $this->testTeam1->id]);

        setPermissionsTeamId($this->testTeam2->id);
        $this->testTeam1->delete();

        setPermissionsTeamId($this->testTeam1->id);
        $this->assertDatabaseMissing('model_has_permissions', [config('permission.column_names.team_foreign_key') => $this->testTeam1->id]);
        $this->assertDatabaseMissing('model_has_roles', [config('permission.column_names.team_foreign_key') => $this->testTeam1->id]);

        setPermissionsTeamId($this->testTeam2->id);
        $this->assertDatabaseHas('model_has_permissions', [config('permission.column_names.team_foreign_key') => $this->testTeam2->id]);
        $this->assertDatabaseHas('model_has_roles', [config('permission.column_names.team_foreign_key') => $this->testTeam2->id]);
    }

    /** @test */
    public function it_deletes_especific_role_entries_when_deleting_teams()
    {
        $roleTeam1 = app(Role::class)->create(['name' => 'testRole3', 'team_test_id' => $this->testTeam1->id]);
        $roleTeam2 = app(Role::class)->create(['name' => 'testRole3', 'team_test_id' => $this->testTeam2->id]);
        $user = User::create(['email' => 'user@test.com']);
        $user->assignRole('testRole3');
        setPermissionsTeamId($this->testTeam2->id);
        $user->assignRole('testRole3');

        $this->assertDatabaseHas('roles', [config('permission.column_names.team_foreign_key') => $this->testTeam1->id]);
        $this->assertDatabaseHas('roles', [config('permission.column_names.team_foreign_key') => $this->testTeam2->id]);
        $this->assertDatabaseHas('model_has_roles', [config('permission.column_names.team_foreign_key') => $this->testTeam1->id]);
        $this->assertDatabaseHas('model_has_roles', [config('permission.column_names.team_foreign_key') => $this->testTeam2->id]);

        $this->testTeam1->delete();

        $this->assertDatabaseMissing('roles', [config('permission.column_names.team_foreign_key') => $this->testTeam1->id]);
        $this->assertDatabaseMissing('model_has_roles', [config('permission.column_names.team_foreign_key') => $this->testTeam1->id]);
        $this->assertDatabaseHas('roles', [config('permission.column_names.team_foreign_key') => $this->testTeam2->id]);
        $this->assertDatabaseHas('model_has_roles', [config('permission.column_names.team_foreign_key') => $this->testTeam2->id]);
    }

    /** @test */
    public function it_does_not_detach_roles_when_soft_deleting()
    {
        $user = User::create(['email' => 'test@example.com']);
        setPermissionsTeamId($this->testTeam1->id);

        $team1 = SoftDeletingTeam::find($this->testTeam1->id);
        $roleTeam1 = app(Role::class)->create(['name' => 'testRole3', 'team_test_id' => $team1->id]);
        $user->assignRole('testRole');

        $team1->delete();

        $team1 = SoftDeletingTeam::onlyTrashed()->find($this->testTeam1->id);

        $this->assertDatabaseHas('model_has_roles', [config('permission.column_names.team_foreign_key') => $team1->id]);
        $this->assertDatabaseHas('roles', [config('permission.column_names.team_foreign_key') => $team1->id]);
    }
}
