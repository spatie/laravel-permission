<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class PermissionDoesNotExist extends InvalidArgumentException
{
    public static function create(string $permissionName, string $guardName = '')
    {
        return new static(trans('permission::exceptions.create', [
            'permissionName' => $permissionName,
            'guardName' => $guardName,
        ]));
    }

    public static function withId(int $permissionId)
    {
        return new static(trans('permission::exceptions.with_id', [
            'permissionId' => $permissionId,
        ]));
    }
}
