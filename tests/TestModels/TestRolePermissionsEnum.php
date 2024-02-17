<?php

namespace Spatie\Permission\Tests\TestModels;

/**
 * Enum example
 *
 * Syntax is `case NAME = VALUE`
 * The NAME will be used in your application code
 * The VALUE will be the role/permission name checked-against in the database.
 *
 * NOTE: When creating roles/permissions, you must manually convert name to value when passing the role/permission name to Eloquent.
 *       eg: use MyEnum::NAME->value when specifying the role/permission name
 *
 * In your application code, when checking for authorization, you can use MyEnum::NAME in most cases.
 * You can always manually fallback to MyEnum::NAME->value when using features that aren't aware of Enum support.
 *
 * TestRolePermissionsEnum::USERMANAGER->name = 'USERMANAGER'
 * TestRolePermissionsEnum::USERMANAGER->value = 'User Manager'  <-- This is the role-name checked by this package
 * TestRolePermissionsEnum::USERMANAGER->label() = 'Manage Users'
 */
enum TestRolePermissionsEnum: string
{
    // case NAME = 'value';
    // case NAMEINAPP = 'name-in-database';

    case WRITER = 'writer';
    case EDITOR = 'editor';
    case USERMANAGER = 'user-manager';
    case ADMIN = 'administrator';
    case CASTED_ENUM_1 = 'casted_enum-1';
    case CASTED_ENUM_2 = 'casted_enum-2';

    case VIEWARTICLES = 'view articles';
    case EDITARTICLES = 'edit articles';

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
            self::WRITER => 'Writers',
            self::EDITOR => 'Editors',
            self::USERMANAGER => 'User Managers',
            self::ADMIN => 'Admins',

            self::VIEWARTICLES => 'View Articles',
            self::EDITARTICLES => 'Edit Articles',
        };
    }
}
