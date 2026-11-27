---
title: Artisan Commands
weight: 10
---

## Creating roles and permissions with Artisan Commands

You can create a role or permission from the console with artisan commands.

```bash
php artisan permission:create-role writer
```

```bash
php artisan permission:create-permission "edit articles"
```

When creating permissions/roles for specific guards you can specify the guard names as a second argument:

```bash
php artisan permission:create-role writer web
```

```bash
php artisan permission:create-permission "edit articles" web
```

When creating roles you can also create and link permissions at the same time:

```bash
php artisan permission:create-role writer web "create articles|edit articles"
```

When creating roles with teams enabled you can set the team id by adding the `--team-id` parameter:

```bash
php artisan permission:create-role --team-id=1 writer
php artisan permission:create-role writer api --team-id=1
```

## Displaying roles and permissions in the console

There is also a `show` command to show a table of roles and permissions per guard:

```bash
php artisan permission:show
```

## Exporting role and permission names

The `export` command writes the names of all roles and permissions to a JSON file. This is useful when a separate frontend (or any other consumer) needs to know which names exist, for example to generate types or to validate permission checks at build time.

```bash
php artisan permission:export
```

By default the file is written to `resources/js/permissions.json`. The names are sorted, duplicates (e.g. the same name on multiple guards or teams) are removed, and the output is pretty-printed with a trailing newline so the file produces clean diffs in version control:

```json
{
    "permissions": [
        "edit articles",
        "publish articles"
    ],
    "roles": [
        "admin",
        "writer"
    ]
}
```

You can write the file to a different location with the `--path` option, limit the export to a single guard with `--guard`, and export only one of the two lists with `--only`:

```bash
php artisan permission:export --path=../frontend/src/permissions.json
php artisan permission:export --guard=api
php artisan permission:export --only=permissions
```

Because the output is deterministic, you can run the command in your CI pipeline after migrating and seeding, and fail the build when the committed file is out of date:

```bash
php artisan permission:export
git diff --exit-code resources/js/permissions.json
```

## Resetting the Cache

When you use the built-in functions for manipulating roles and permissions, the cache is automatically reset for you, and relations are automatically reloaded for the current model record.

See the Advanced-Usage/Cache section of these docs for detailed specifics.

If you need to manually reset the cache for this package, you may use the following artisan command:

```bash
php artisan permission:cache-reset
```

Again, it is more efficient to use the API provided by this package, instead of manually clearing the cache.
