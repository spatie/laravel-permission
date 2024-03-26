<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\PermissionRegistrar;

class CacheReset extends Command
{
    protected $signature = 'permission:cache-reset
        {--p|permission-registrar=}';

    protected $description = 'Reset the permission cache';

    public function handle()
    {
        /** @var PermissionRegistrar $permissionRegistrar */
        $permissionRegistrar = app($this->option('permission-registrar') ?? PermissionRegistrar::class);

        if ($permissionRegistrar->forgetCachedPermissions()) {
            $this->info('Permission cache flushed.');
        } else {
            $this->error('Unable to flush cache.');
        }
    }
}
