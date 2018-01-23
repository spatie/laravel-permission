<?php

namespace Spatie\Permission\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedException extends HttpException
{
    public static function forRoles(array $roles): self
    {
        $permStr = '';
        if (config('permission.display_permission_in_exception')) {
            $permStr = ' Necessary roles are '.implode(', ', $roles).'.';
        }

        return new static(403, 'User does not have the right roles.'.$permStr, null, []);
    }

    public static function forPermissions(array $permissions): self
    {
        $permStr = '';
        if (config('permission.display_permission_in_exception')) {
            $permStr = ' Necessary permissions are '.implode(', ', $permissions).'.';
        }

        return new static(403, 'User does not have the right permissions.'.$permStr, null, []);
    }

    public static function notLoggedIn(): self
    {
        return new static(403, 'User is not logged in.', null, []);
    }
}
