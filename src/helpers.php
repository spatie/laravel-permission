<?php

/**
 * @param string $guard
 *
 * @return string|null
 */
function getModelForGuard(string $guard)
{
    return collect(config('auth.guards'))
        ->map(function ($guard) {
            return config("auth.providers.{$guard['provider']}.model");
        })->get($guard);
}

/**
 * Pass in a user instance, receive a nicely-formatted list of the user's roles.
 *
 * @param App\User $user
 *
 * @return string
 */
function getRoleNames(App\User $user): string
{
    if ($user) {
        return ucwords($user->roles->pluck('name')->implode(', '));
    }

    return '';
}

/**
 * Pass in a user instance, receive a raw list of the user's roles.
 *
 * @param App\User $user
 *
 * @return string
 */
function getRoleNamesRaw(App\User $user): string
{
    if ($user) {
        return $user->roles->pluck('name');
    }

    return '';
}
