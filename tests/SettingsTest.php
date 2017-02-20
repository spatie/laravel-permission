<?php

namespace Spatie\Permission\Test;

use Monolog\Logger;

class SettingsTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /** @test */
    public function it_logs_when_config_is_set_to_true()
    {
        app()->config['laravel-permission.settings.logging'] = true;

        (new \CreatePermissionTables())->down();

        $this->reloadPermissions();

        $this->assertLogged('Could not register permissions', Logger::ALERT);
    }

    /** @test */
    public function it_doesnt_log_when_config_is_set_to_false()
    {
        app()->config['laravel-permission.settings.logging'] = false;

        (new \CreatePermissionTables())->down();

        $this->reloadPermissions();

        $this->assertNotLogged('Could not register permissions', Logger::ALERT);
    }
}
