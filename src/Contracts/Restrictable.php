<?php

namespace Spatie\Permission\Contracts;


/**
 * Every resource which can be targeted by a Permission or a Role must implement this interface.
 *
 * @see \Spatie\Permission\Contracts\Permission
 * @see \Spatie\Permission\Contracts\Role
 * @package Spatie\Permission\Contracts
 */
interface Restrictable
{
    /**
     * Get the unique identifier value for the restrictable resource.
     *
     * @return int
     */
    public function getRestrictableId(): int;

    /**
     * Get the name of the table for the restrictable resource.
     *
     * @return string
     */
    public function getRestrictableTable(): string;
}