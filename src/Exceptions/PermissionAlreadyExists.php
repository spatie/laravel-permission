<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class PermissionAlreadyExists extends InvalidArgumentException
{
    public static function create(string $permissionName, string $guardName)
    {
         return new static(trans('permission::exceptions.permission_already_exists-create', [
            'permissionName' => $permissionName,
            'guardName' => $guardName,
        ]));
    }
}
