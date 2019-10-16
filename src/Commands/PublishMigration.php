<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\PermissionServiceProvider;

class PublishMigration extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'permission:migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish the laravel-permission migration';

    public function handle()
    {
        Artisan::call('vendor:publish', [
            '--provider' => PermissionServiceProvider::class,
            '--tag' => 'migrations',
        ]);
    }
}
