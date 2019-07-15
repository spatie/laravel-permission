---
title: Using artisan commands
weight: 7
---

You can create a role or permission from a console with artisan commands.

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
