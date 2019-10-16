<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\PermissionServiceProvider;

class Publish extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'permission:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish the laravel-permission config file and migration';

    public function handle()
    {
        Artisan::call('vendor:publish', [
            "--provider" => PermissionServiceProvider::class,
        ]);
    }
}
