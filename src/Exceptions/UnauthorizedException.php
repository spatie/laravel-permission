<?php

namespace Spatie\Permission\Exceptions;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Spatie\Permission\Support\Config;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedException extends HttpException
{
    private array $requiredRoles = [];

    private array $requiredPermissions = [];

    public static function forRoles(array $roles): static
    {
        $message = __('User does not have the right roles.');

        if (Config::displayRoleInException()) {
            $message .= ' '.__('Necessary roles are :roles', ['roles' => implode(', ', $roles)]);
        }

        $exception = new static(403, $message, null, []);
        $exception->requiredRoles = $roles;

        return $exception;
    }

    public static function forPermissions(array $permissions): static
    {
        $message = __('User does not have the right permissions.');

        if (Config::displayPermissionInException()) {
            $message .= ' '.__('Necessary permissions are :permissions', ['permissions' => implode(', ', $permissions)]);
        }

        $exception = new static(403, $message, null, []);
        $exception->requiredPermissions = $permissions;

        return $exception;
    }

    public static function forRolesOrPermissions(array $rolesOrPermissions): static
    {
        $message = __('User does not have any of the necessary access rights.');

        if (Config::displayPermissionInException() && Config::displayRoleInException()) {
            $message .= ' '.__('Necessary roles or permissions are :values', ['values' => implode(', ', $rolesOrPermissions)]);
        }

        $exception = new static(403, $message, null, []);
        $exception->requiredPermissions = $rolesOrPermissions;

        return $exception;
    }

    public static function missingTraitHasRoles(Authorizable $user): static
    {
        return new static(403, __('Authorizable class `:class` must use Spatie\\Permission\\Traits\\HasRoles trait.', [
            'class' => $user::class,
        ]), null, []);
    }

    public static function notLoggedIn(): static
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
