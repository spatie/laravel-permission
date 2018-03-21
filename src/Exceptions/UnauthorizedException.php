<?php

namespace Spatie\Permission\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedException extends HttpException
{
    private $requiredRoles = [];

    private $requiredPermissions = [];

    public static function forRoles(array $roles): self
    {
        $message = trans('permission::exceptions.unauthorized_exception-for_roles');

        if (config('permission.display_permission_in_exception')) {
            $permStr = implode(', ', $roles);
            $message .= ' ' . trans('permission::exceptions.unauthorized_exception-for_roles-display_permission', [
                'permStr' => $permStr,
            ]);
        }

        $exception = new static(403, $message, null, []);
        $exception->requiredRoles = $roles;

        return $exception;
    }

    public static function forPermissions(array $permissions): self
    {
        $message = trans('permission::exceptions.unauthorized_exception-for_permissions');

        if (config('permission.display_permission_in_exception')) {
            $permStr = implode(', ', $permissions);
            $message .= ' ' . trans('permission::exceptions.unauthorized_exception-for_permissions-display_permission', [
                'permStr' => $permStr,
            ]);
        }

        $exception = new static(403, $message, null, []);
        $exception->requiredPermissions = $permissions;

        return $exception;
    }

    public static function notLoggedIn(): self
    {
        return new static(403, trans('permission::exceptions.unauthorized_exception-not_logged_in'), null, []);
    }

    public function getRequiredRoles(): array
    {
        return $this->requiredRoles;
    }

    public function getRequiredPermissions(): array
    {
        return $this->requiredPermissions;
    }
}
