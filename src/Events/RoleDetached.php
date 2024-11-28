<?php

declare(strict_types=1);

namespace Spatie\Permission\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spatie\Permission\Contracts\Role;

class RoleDetached
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Model $model, public Role $role)
    {}
}
