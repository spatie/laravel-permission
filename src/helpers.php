<?php

/**
 * @param string $guard
 *
 * @return string|null
 */
function getProviderModelForGuard(string $guard = null)
{
    if (null === $guard) {
        $guard = config('auth.defaults.guard');
    }

    return collect(config('auth.guards'))
        ->map(function ($guard) {
            if (! isset($guard['provider'])) {
                return;
            }

            return config("auth.providers.{$guard['provider']}.model");
        })->get($guard);
}

function isNotLumen() : bool
{
    return ! preg_match('/lumen/i', app()->version());
}
