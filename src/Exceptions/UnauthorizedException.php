<?php

namespace Spatie\Permission\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedException extends HttpException
{
    public static function forRoles(array $roles): self
    {
        return new static(403, 'User does not have the right roles.', null, []);
    }

    public static function forPermissions(array $permissions): self
    {
        return new static(403, 'User does not have the right permissions.', null, []);
    }

    public static function notLoggedIn(): self
    {
        return new static(403, 'User is not logged in.', null, []);
    }
}
