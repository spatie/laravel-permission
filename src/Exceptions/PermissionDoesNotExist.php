<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class PermissionDoesNotExist extends InvalidArgumentException
{
    public static function create(string $permissionName, string $guardName = '')
    {
        return new static(trans('permission::exceptions.permission_does_not_exist-create', [
            'permissionName' => $permissionName,
            'guardName' => $guardName,
        ]));
    }

    public static function withId(int $permissionId)
    {
        return new static(trans('permission::exceptions.permission_does_not_exist-with_id', [
            'permissionId' => $permissionId,
        ]));
    }
}
