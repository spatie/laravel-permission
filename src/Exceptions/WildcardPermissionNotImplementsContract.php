<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class WildcardPermissionNotImplementsContract extends InvalidArgumentException
{
    public static function create()
    {
        return new static(__('Wildcard permission class must implement Spatie\\Permission\\Contracts\\Wildcard contract'));
    }
}
