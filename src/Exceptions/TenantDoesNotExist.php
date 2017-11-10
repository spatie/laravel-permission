<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class TenantDoesNotExist extends InvalidArgumentException
{
    public static function create(string $tenantName)
    {
        return new static("There is no tenant named `{$tenantName}`.");
    }
}
