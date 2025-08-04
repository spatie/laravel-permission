<?php

namespace Spatie\Permission\Exceptions;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedException extends HttpException
{
    private $requiredRoles = [];

    private $requiredPermissions = [];

    public static function forRoles(array $roles): self
    {
        $message = __('User does not have the right roles.');

        if (config('permission.display_role_in_exception')) {
            $message .= ' '.__('Necessary roles are :roles', ['roles' => implode(', ', $roles)]);
        }

        $exception = new static(403, $message, null, []);
        $exception->requiredRoles = $roles;

        return $exception;
    }

    public static function forPermissions(array $permissions): self
    {
        $message = __('User does not have the right permissions.');

        if (config('permission.display_permission_in_exception')) {
            $message .= ' '.__('Necessary permissions are :permissions', ['permissions' => implode(', ', $permissions)]);
        }

        $exception = new static(403, $message, null, []);
        $exception->requiredPermissions = $permissions;

        return $exception;
    }

    public static function forRolesOrPermissions(array $rolesOrPermissions): self
    {
        $message = __('User does not have any of the necessary access rights.');

        if (config('permission.display_permission_in_exception') && config('permission.display_role_in_exception')) {
            $message .= ' '.__('Necessary roles or permissions are :values', ['values' => implode(', ', $rolesOrPermissions)]);
        }

        $exception = new static(403, $message, null, []);
        $exception->requiredPermissions = $rolesOrPermissions;

        return $exception;
    }

    public static function missingTraitHasRoles(Authorizable $user): self
    {
        $class = get_class($user);

        return new static(403, __('Authorizable class `:class` must use Spatie\\Permission\\Traits\\HasRoles trait.', [
            'class' => $class,
        ]), null, []);
    }

    public static function notLoggedIn(): self
    {
        return new static(403, __('User is not logged in.'), null, []);
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
