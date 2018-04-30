<?php
/**
 * Copyright (c) Padosoft.com 2018.
 */

namespace Spatie\Permission\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class PermissionAssigned
{
    use SerializesModels;

    /**
     * @var Collection
     */
    public $permissions;
    /**
     * @var Model
     */
    public $target;

    public function __construct(Collection $permissions, Model $target)
    {
        $this->permissions = $permissions;
        $this->target = $target;
    }

}
