<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class TenantAlreadyExist extends InvalidArgumentException
{
    public static function create(string $tenantName)
    {
        return new static("A tenant `{$tenantName}` already exists.");
    }
}
