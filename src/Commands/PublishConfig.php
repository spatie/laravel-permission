<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\PermissionServiceProvider;

class PublishConfig extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'permission:config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish the laravel-permission config file';

    public function handle()
    {
        Artisan::call('vendor:publish', [
            "--provider" => PermissionServiceProvider::class,
            "--tag" => 'config',
        ]);
    }
}
