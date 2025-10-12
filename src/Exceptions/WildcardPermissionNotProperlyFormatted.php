<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class WildcardPermissionNotProperlyFormatted extends InvalidArgumentException
{
    public static function create(string $permission)
    {
        return new static(__('Wildcard permission `:permission` is not properly formatted.', [
            'permission' => $permission,
        ]));
    }
}
