---
title: Model Policies
weight: 2
---

The best way to incorporate access control for application features is with [Laravel's Model Policies](https://laravel.com/docs/authorization#creating-policies).

Using Policies allows you to simplify things by abstracting your "control" rules into one place, where your application logic can be combined with your permission rules.

Jeffrey Way explains the concept simply in the [Laravel 6 Authorization Filters](https://laracasts.com/series/laravel-6-from-scratch/episodes/51) and [policies](https://laracasts.com/series/laravel-6-from-scratch/episodes/63) videos and in other related lessons in that chapter. He also mentions how to set up a super-admin, both in a model policy and globally in your application.

Here's an example of a PostPolicy which could control access to Post model records:
```php
<?php
namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    public function view(?User $user, Post $post): bool
    {
        if ($post->published) {
            return true;
        }

        // visitors cannot view unpublished items
        if ($user === null) {
            return false;
        }

        // admin overrides published status
        if ($user->can('view unpublished posts')) {
            return true;
        }

        // authors can view their own unpublished posts
        return $user->id == $post->user_id;
    }

    public function create(User $user): bool
    {
        return $user->can('create posts');
    }

    public function update(User $user, Post $post): bool
    {
        if ($user->can('edit all posts')) {
            return true;
        }

        if ($user->can('edit own posts')) {
            return $user->id == $post->user_id;
        }
    }

    public function delete(User $user, Post $post): bool
    {
        if ($user->can('delete any post')) {
            return true;
        }

        if ($user->can('delete own posts')) {
            return $user->id == $post->user_id;
        }
    }
}
```
