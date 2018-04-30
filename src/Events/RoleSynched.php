<?php
/**
 * Copyright (c) Padosoft.com 2018.
 */

namespace Spatie\Permission\Events;

use Illuminate\Support\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;

class RoleSynched
{
    use SerializesModels;

    /**
     * @var Collection
     */
    public $roles_assigned;
    /**
     * @var Collection
     */
    public $roles_revoked;
    /**
     * @var Collection
     */
    public $roles_added;
    /**
     * @var Model
     */
    public $target;

    public function __construct(Collection $roles_revoked, Collection $roles_added, Collection $roles_assigned, Model $target)
    {
        $this->roles_revoked = $roles_revoked;
        $this->roles_added = $roles_added;
        $this->roles_assigned = $roles_assigned;
        $this->target = $target;
    }

}
