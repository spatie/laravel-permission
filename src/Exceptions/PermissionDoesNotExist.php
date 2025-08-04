<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class PermissionDoesNotExist extends InvalidArgumentException
{
    public static function create(string $permissionName, ?string $guardName)
    {
        return new static(__('There is no permission named `:permission` for guard `:guard`.', [
            'permission' => $permissionName,
            'guard' => $guardName,
        ]));
    }

    /**
     * @param  int|string  $permissionId
     * @return static
     */
    public static function withId($permissionId, ?string $guardName)
    {
        return new static(__('There is no [permission] with ID `:id` for guard `:guard`.', [
            'id' => $permissionId,
            'guard' => $guardName,
        ]));
    }
}
