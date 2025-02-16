<?php

declare(strict_types=1);

namespace Spatie\Permission\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Spatie\Permission\Contracts\Permission;

class PermissionAttached
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Internally the HasPermissions trait passes an array of permission ids (eg: int's or uuid's)
     * Theoretically one could register the event to other places and pass an Eloquent record.
     * So a Listener should inspect the type of $permissionsOrIds received before using.
     *
     * @param  array|int[]|string[]|Permission|Permission[]|Collection  $permissionsOrIds
     */
    public function __construct(public Model $model, public mixed $permissionsOrIds) {}
}
