<?php

namespace Spatie\Permission\Traits;

trait HandleUnauthorized
{
    public function handleUnauthorized()
    {
        $unauthorizedRedirectUrl = config('permission.redirect_unauthorized_users_to_url');
        if (! is_null($unauthorizedRedirectUrl)) {
            return redirect($unauthorizedRedirectUrl);
        }
        abort(403);
    }

}