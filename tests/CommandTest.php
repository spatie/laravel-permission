<?php

namespace Spatie\Permission\Tests;

use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CommandTest extends TestCase
{
    /** @test */
    #[Test]
    public function it_can_create_a_role()
    {
        Artisan::call('permission:create-role', ['name' => 'new-role']);

        $this->assertCount(1, Role::where('name', 'new-role')->get());
        $this->assertCount(0, Role::where('name', 'new-role')->first()->permissions);
    }

    /** @test */
    #[Test]
    public function it_can_create_a_role_with_a_specific_guard()
    {
        Artisan::call('permission:create-role', [
            'name' => 'new-role',
            'guard' => 'api',
        ]);

        $this->assertCount(1, Role::where('name', 'new-role')
            ->where('guard_name', 'api')
            ->get());
    }

    /** @test */
    #[Test]
    public function it_can_create_a_permission()
    {
        Artisan::call('permission:create-permission', ['name' => 'new-permission']);

        $this->assertCount(1, Permission::where('name', 'new-permission')->get());
    }

    /** @test */
    #[Test]
    public function it_can_create_a_permission_with_a_specific_guard()
    {
        Artisan::call('permission:create-permission', [
            'name' => 'new-permission',
            'guard' => 'api',
        ]);

        $this->assertCount(1, Permission::where('name', 'new-permission')
            ->where('guard_name', 'api')
            ->get());
    }

    /** @test */
    #[Test]
    public function it_can_create_a_role_and_permissions_at_same_time()
    {
        Artisan::call('permission:create-role', [
            'name' => 'new-role',
            'permissions' => 'first permission | second permission',
        ]);

        $role = Role::where('name', 'new-role')->first();

        $this->assertTrue($role->hasPermissionTo('first permission'));
        $this->assertTrue($role->hasPermissionTo('second permission'));
    }

    /** @test */
    #[Test]
    public function it_can_create_a_role_without_duplication()
    {
        Artisan::call('permission:create-role', ['name' => 'new-role']);
        Artisan::call('permission:create-role', ['name' => 'new-role']);

        $this->assertCount(1, Role::where('name', 'new-role')->get());
        $this->assertCount(0, Role::where('name', 'new-role')->first()->permissions);
    }

    /** @test */
    #[Test]
    public function it_can_create_a_permission_without_duplication()
    {
        Artisan::call('permission:create-permission', ['name' => 'new-permission']);
        Artisan::call('permission:create-permission', ['name' => 'new-permission']);

        $this->assertCount(1, Permission::where('name', 'new-permission')->get());
    }

    /** @test */
    #[Test]
    public function it_can_show_permission_tables()
    {
        Role::where('name', 'testRole2')->delete();
        Role::create(['name' => 'testRole_2']);

        Artisan::call('permission:show');

        $output = Artisan::output();

        $this->assertTrue(strpos($output, 'Guard: web') !== false);
        $this->assertTrue(strpos($output, 'Guard: admin') !== false);

        // |               | testRole | testRole_2 |
        // | edit-articles |  ·       |  ·         |
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('/\|\s+\|\s+testRole\s+\|\s+testRole_2\s+\|/', $output);
            $this->assertMatchesRegularExpression('/\|\s+edit-articles\s+\|\s+·\s+\|\s+·\s+\|/', $output);
        } else { // phpUnit 9/8
            $this->assertRegExp('/\|\s+\|\s+testRole\s+\|\s+testRole_2\s+\|/', $output);
            $this->assertRegExp('/\|\s+edit-articles\s+\|\s+·\s+\|\s+·\s+\|/', $output);
        }

        Role::findByName('testRole')->givePermissionTo('edit-articles');
        $this->reloadPermissions();

        Artisan::call('permission:show');

        $output = Artisan::output();

        // | edit-articles |  ·       |  ·        |
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('/\|\s+edit-articles\s+\|\s+✔\s+\|\s+·\s+\|/', $output);
        } else {
            $this->assertRegExp('/\|\s+edit-articles\s+\|\s+✔\s+\|\s+·\s+\|/', $output);
        }
    }

    /** @test */
    #[Test]
    public function it_can_show_permissions_for_guard()
    {
        Artisan::call('permission:show', ['guard' => 'web']);

        $output = Artisan::output();

        $this->assertTrue(strpos($output, 'Guard: web') !== false);
        $this->assertTrue(strpos($output, 'Guard: admin') === false);
    }

    /** @test */
    #[Test]
    public function it_can_setup_teams_upgrade()
    {
        config()->set('permission.teams', true);

        $this->artisan('permission:setup-teams')
            ->expectsQuestion('Proceed with the migration creation?', 'yes')
            ->assertExitCode(0);

        $matchingFiles = glob(database_path('migrations/*_add_teams_fields.php'));
        $this->assertTrue(count($matchingFiles) > 0);

        $AddTeamsFields = require $matchingFiles[count($matchingFiles) - 1];
        $AddTeamsFields->up();
        $AddTeamsFields->up(); // test upgrade teams migration fresh

        Role::create(['name' => 'new-role', 'team_test_id' => 1]);
        $role = Role::where('name', 'new-role')->first();
        $this->assertNotNull($role);
        $this->assertSame(1, (int) $role->team_test_id);

        // remove migration
        foreach ($matchingFiles as $file) {
            unlink($file);
        }
    }

    /** @test */
    #[Test]
    public function it_can_show_roles_by_teams()
    {
        config()->set('permission.teams', true);
        app(\Spatie\Permission\PermissionRegistrar::class)->initializeCache();

        Role::where('name', 'testRole2')->delete();
        Role::create(['name' => 'testRole_2']);
        Role::create(['name' => 'testRole_Team', 'team_test_id' => 1]);
        Role::create(['name' => 'testRole_Team', 'team_test_id' => 2]); // same name different team
        Artisan::call('permission:show');

        $output = Artisan::output();

        // |    | Team ID: NULL         | Team ID: 1    | Team ID: 2    |
        // |    | testRole | testRole_2 | testRole_Team | testRole_Team |
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('/\|\s+\|\s+Team ID: NULL\s+\|\s+Team ID: 1\s+\|\s+Team ID: 2\s+\|/', $output);
            $this->assertMatchesRegularExpression('/\|\s+\|\s+testRole\s+\|\s+testRole_2\s+\|\s+testRole_Team\s+\|\s+testRole_Team\s+\|/', $output);
        } else { // phpUnit 9/8
            $this->assertRegExp('/\|\s+\|\s+Team ID: NULL\s+\|\s+Team ID: 1\s+\|\s+Team ID: 2\s+\|/', $output);
            $this->assertRegExp('/\|\s+\|\s+testRole\s+\|\s+testRole_2\s+\|\s+testRole_Team\s+\|\s+testRole_Team\s+\|/', $output);
        }
    }

    /** @test */
    #[Test]
    public function it_can_respond_to_about_command_with_default()
    {
        if (! class_exists(InstalledVersions::class) || ! class_exists(AboutCommand::class)) {
            $this->markTestSkipped();
        }
        if (! method_exists(AboutCommand::class, 'flushState')) {
            $this->markTestSkipped();
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->initializeCache();

        Artisan::call('about');
        $output = str_replace("\r\n", "\n", Artisan::output());

        $pattern = '/Spatie Permissions[ .\n]*Features Enabled[ .]*Default[ .\n]*Version/';
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression($pattern, $output);
        } else { // phpUnit 9/8
            $this->assertRegExp($pattern, $output);
        }
    }

    /** @test */
    #[Test]
    public function it_can_respond_to_about_command_with_teams()
    {
        if (! class_exists(InstalledVersions::class) || ! class_exists(AboutCommand::class)) {
            $this->markTestSkipped();
        }
        if (! method_exists(AboutCommand::class, 'flushState')) {
            $this->markTestSkipped();
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->initializeCache();

        config()->set('permission.teams', true);

        Artisan::call('about');
        $output = str_replace("\r\n", "\n", Artisan::output());

        $pattern = '/Spatie Permissions[ .\n]*Features Enabled[ .]*Teams[ .\n]*Version/';
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression($pattern, $output);
        } else { // phpUnit 9/8
            $this->assertRegExp($pattern, $output);
        }
    }
}
