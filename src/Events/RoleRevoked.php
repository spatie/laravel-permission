<?php
/**
 * Copyright (c) Padosoft.com 2018.
 */

namespace Spatie\Permission\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class RoleRevoked
{
    use SerializesModels;

    /**
     * @var Collection
     */
    public $roles;
    /**
     * @var Model
     */
    public $target;

    public function __construct(Collection $roles, Model $target)
    {
        $this->roles = $roles;
        $this->target = $target;
    }

}
