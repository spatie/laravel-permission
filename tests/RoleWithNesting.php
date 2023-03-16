<?php

namespace Spatie\Permission\Test;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property Collection $parents
 * @property Collection $children
 */
class RoleWithNesting extends \Spatie\Permission\Models\Role
{
    const HIERARCHY_TABLE = 'roles_hierarchy';

    /**
     * @return BelongsToMany
     */
    public function parents()
    {
        return $this->belongsToMany(
            static::class,
            static::HIERARCHY_TABLE,
            'child_id',
            'parent_id');
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(
            static::class,
            static::HIERARCHY_TABLE,
            'parent_id',
            'child_id');
    }
}
