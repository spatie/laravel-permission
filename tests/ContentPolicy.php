<?php

namespace Spatie\Permission\Tests;

use Illuminate\Contracts\Auth\Access\Authorizable;

class ContentPolicy
{
    public function before(Authorizable $user, string $ability): ?bool
    {
        return $user->hasRole('testAdminRole', 'admin') ?: null;
    }

    public function view($user, $content)
    {
        return $user->id === $content->user_id;
    }

    public function update($user, $modelRecord): bool
    {
        return $user->id === $modelRecord->user_id || $user->can('edit-articles');
    }
}
