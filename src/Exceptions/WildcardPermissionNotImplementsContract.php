<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class WildcardPermissionNotImplementsContract extends InvalidArgumentException
{
    public static function create(): WildcardPermissionNotImplementsContract
    {
        return new static('Wildcard permission class must implements Spatie\Permission\Contracts\Wildcard contract');
    }
}
