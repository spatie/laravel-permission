<?php

namespace Spatie\Permission\Tests;

use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Tests\TestModels\User;

class TeamCommandTest extends TestCase
{
    protected $hasTeams = true;

    /** @test */
    #[Test]
    public function it_can_assign_role_to_user_with_team_id()
    {
        $user = User::first();

        Artisan::call('permission:assign-role', [
            'name' => 'testRole',
            'userId' => $user->id,
            'guard' => 'web',
            'userModelNamespace' => User::class,
            '--team-id' => 1,
        ]);

        $output = Artisan::output();

        $this->assertStringContainsString('Role `testRole` assigned to user ID '.$user->id.' successfully.', $output);

        setPermissionsTeamId(1);
        $user->unsetRelation('roles');
        $this->assertTrue($user->hasRole('testRole'));
    }

    /** @test */
    #[Test]
    public function it_can_assign_role_to_user_on_different_teams()
    {
        $user = User::first();

        Artisan::call('permission:assign-role', [
            'name' => 'testRole',
            'userId' => $user->id,
            'guard' => 'web',
            'userModelNamespace' => User::class,
            '--team-id' => 1,
        ]);

        Artisan::call('permission:assign-role', [
            'name' => 'testRole2',
            'userId' => $user->id,
            'guard' => 'web',
            'userModelNamespace' => User::class,
            '--team-id' => 2,
        ]);

        setPermissionsTeamId(1);
        $user->unsetRelation('roles');
        $this->assertTrue($user->hasRole('testRole'));
        $this->assertFalse($user->hasRole('testRole2'));

        setPermissionsTeamId(2);
        $user->unsetRelation('roles');
        $this->assertTrue($user->hasRole('testRole2'));
        $this->assertFalse($user->hasRole('testRole'));
    }

    /** @test */
    #[Test]
    public function it_restores_previous_team_id_after_assigning_role()
    {
        $user = User::first();

        setPermissionsTeamId(5);

        Artisan::call('permission:assign-role', [
            'name' => 'testRole',
            'userId' => $user->id,
            'guard' => 'web',
            'userModelNamespace' => User::class,
            '--team-id' => 1,
        ]);

        $this->assertEquals(5, getPermissionsTeamId());
    }
}
