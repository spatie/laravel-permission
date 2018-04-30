<?php
/**
 * Copyright (c) Padosoft.com 2018.
 */

namespace Spatie\Permission\Events;

use Illuminate\Support\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;

class PermissionSynched
{
    use SerializesModels;

    /**
     * @var Collection
     */
    public $permissions_assigned;
    /**
     * @var Collection
     */
    public $permissions_revoked;
    /**
     * @var Collection
     */
    public $permissions_added;
    /**
     * @var Model
     */
    public $target;

    public function __construct(Collection $permissions_revoked, Collection $permissions_added, Collection $permissions_assigned, Model $target)
    {
        $this->permissions_revoked = $permissions_revoked;
        $this->permissions_added = $permissions_added;
        $this->permissions_assigned = $permissions_assigned;
        $this->target = $target;
    }

}
