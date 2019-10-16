<?php

namespace Spatie\Permission\Test;

use Illuminate\Support\Str;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

class PublishTest extends TestCase
{
    /** @test */
    public function it_can_publish_the_config_file_and_migration_with_the_permission_publish_shortcut()
    {
        $this->clearConfigFile();
        $this->clearMigrationFile();

        $this->artisan('permission:publish');

        $this->assertConfigFileExists();
        $this->assertMigrationFileExists();
    }

    /** @test */
    public function it_can_publish_the_config_file_with_the_permission_config_shortcut()
    {
        $this->clearConfigFile();

        $this->artisan('permission:config');

        $this->assertConfigFileExists();
    }

    /** @test */
    public function it_can_publish_the_migration_with_the_permission_migration_shortcut()
    {
        $this->clearMigrationFile();

        $this->artisan('permission:migration');

        $this->assertMigrationFileExists();
    }

    private function assertConfigFileExists()
    {
        $filesystem = new Filesystem(new Local(config_path()));
        $this->assertTrue($filesystem->has('permission.php'), 'Failed to locate config file.');
    }

    private function assertMigrationFileExists()
    {
        $filesystem = new Filesystem(new Local(database_path('migrations')));

        foreach ($filesystem->listContents() as $file) {
            if (Str::endsWith($file['path'], '_create_permission_tables.php')) {
                $this->assertTrue(true);
                return;
            }
        }

        $this->fail('Failed to locate migration file.');
    }

    private function clearConfigFile()
    {
        $filesystem = new Filesystem(new Local(config_path()));
        if ($filesystem->has('permission.php')) {
            $filesystem->delete('permission.php');
        }
    }

    private function clearMigrationFile()
    {
        $filesystem = new Filesystem(new Local(database_path('migrations')));

        foreach ($filesystem->listContents() as $file) {
            if (Str::endsWith($file['path'], '_create_permission_tables.php')) {
                $filesystem->delete($file['path']);
            }
        }
    }
}
