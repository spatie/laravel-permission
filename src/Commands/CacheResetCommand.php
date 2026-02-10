<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\PermissionRegistrar;

class CacheResetCommand extends Command
{
    protected $signature = 'permission:cache-reset';

    protected $description = 'Reset the permission cache';

    public function handle(): int
    {
        $permissionRegistrar = app(PermissionRegistrar::class);
        $cacheExists = $permissionRegistrar->getCacheRepository()->has($permissionRegistrar->cacheKey);

        if ($permissionRegistrar->forgetCachedPermissions()) {
            $this->info('Permission cache flushed.');
        } elseif ($cacheExists) {
            $this->error('Unable to flush cache.');
        }

        return self::SUCCESS;
    }
}
