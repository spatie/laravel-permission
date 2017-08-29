<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class GroupDoesNotExist extends InvalidArgumentException
{
    public static function create(string $roleName)
    {
        return new static("There is no group named `{$roleName}`.");
    }
}
