<?php

namespace Spatie\Permission\Traits;

use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;
use Spatie\Permission\PermissionRegistrar;

/**
 * This trait is meant to be used to implement the Restrictable interface based on a Model instance.
 *
 * @see \Spatie\Permission\Contracts\Restrictable
 * @see \Illuminate\Database\Eloquent\Model
 * @package Spatie\Permission\Traits
 */
trait RestrictableTrait
{
    /**
     * Get the unique identifier value for the restrictable resource.
     *
     * @return int
     */
    public function getRestrictableId(): int
    {
        return $this->{$this->primaryKey};
    }

    /**
     * Get the name of the table for the restrictable resource.
     *
     * @return string
     */
    public function getRestrictableTable(): string
    {
        return $this->getTable();
    }
}
