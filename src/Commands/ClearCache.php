<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use \Spatie\Permission\Traits\HasPermissions;

class ClearCache extends Command
{
    use HasPermissions;

    protected $signature = 'permission:clear-cache';


    protected $description = 'Clear permissions cache';

    public function handle()
    {
        $this->forgetCachedPermissions();
        $this->info("Permissions chache was cleard");
    }
}
