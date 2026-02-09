<?php

namespace Spatie\Permission\Tests\TestSupport\TestModels;

use Illuminate\Support\Str;

enum TestRolePermissionsEnum: string
{
    // case NAME = 'value';
    // case NAMEINAPP = 'name-in-database';

    case Writer = 'writer';
    case Editor = 'editor';
    case UserManager = 'user-manager';
    case Admin = 'administrator';
    case CastedEnum1 = 'casted_enum-1';
    case CastedEnum2 = 'casted_enum-2';

    case ViewArticles = 'view articles';
    case EditArticles = 'edit articles';

    case WildcardArticlesCreator = 'articles.edit,view,create';
    case WildcardNewsEverything = 'news.*';
    case WildcardPostsEverything = 'posts.*';

    case WildcardPostsCreate = 'posts.create';
    case WildcardArticlesView = 'articles.view';
    case WildcardProjectsView = 'projects.view';

    // extra helper to allow for greater customization of displayed values, without disclosing the name/value data directly
    public function label(): string
    {
        return match ($this) {
            self::Writer => 'Writers',
            self::Editor => 'Editors',
            self::UserManager => 'User Managers',
            self::Admin => 'Admins',

            self::ViewArticles => 'View Articles',
            self::EditArticles => 'Edit Articles',

            default => Str::words($this->value),
        };
    }
}
