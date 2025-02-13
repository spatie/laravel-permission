<?php

declare(strict_types=1);

namespace Spatie\Permission\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Spatie\Permission\Contracts\Permission;

class PermissionDetached
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @param Model $model
     * @param Permission|Permission[]|Collection $permission
     */
    public function __construct(public Model $model, public mixed $permission)
    {}
}
