<?php

namespace Spatie\Permission\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedException extends HttpException
{
    public static function forRoles(array $roles): self
    {

        if(config('permission.display_permission_in_exception')) {
            $permStr = implode(", ", $roles);
            return new static(403, 'User does not have the right roles. Necessary roles are ' . $permStr, null, []);
        }

        return new static(403, 'User does not have the right roles.', null, []);
    }

    public static function forPermissions(array $permissions): self
    {

        if(true || config('permission.display_permission_in_exception')) {
            $permStr = implode(", ", $permissions);
            return new static(403, 'User does not have the right permissions. Necessary permissions are ' . $permStr, null, []);
        }

       return new static(403, 'User does not have the right permissions.', null, []);
    }

    public static function notLoggedIn(): self
    {
        return new static(403, 'User is not logged in.', null, []);
    }
}

