<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class GroupAlreadyExists extends InvalidArgumentException
{
    public static function create(string $roleName, string $guardName)
    {
        return new static("A group `{$roleName}` already exists for guard `{$guardName}`.");
    }
}
