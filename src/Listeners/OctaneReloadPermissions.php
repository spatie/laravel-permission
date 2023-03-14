<?php

namespace Spatie\Permission\Listeners;

use Spatie\Permission\PermissionRegistrar;

class OctaneReloadPermissions
{
    public function handle($event): void
    {
        $event->sandbox->make(PermissionRegistrar::class)->clearPermissionsCollection();
    }
}
