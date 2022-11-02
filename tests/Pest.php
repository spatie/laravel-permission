<?php

use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses(Spatie\Permission\Test\TestCase::class)->in('.');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

function getWriter()
{
    test()->testUser->assignRole('writer');

    return test()->testUser;
}

function getMember()
{
    test()->testUser->assignRole('member');

    return test()->testUser;
}

function getSuperAdmin()
{
    test()->testAdmin->assignRole('super-admin');

    return test()->testAdmin;
}

function renderView($view, $parameters)
{
    Artisan::call('view:clear');

    if (is_string($view)) {
        $view = view($view)->with($parameters);
    }

    return trim((string) ($view));
}
