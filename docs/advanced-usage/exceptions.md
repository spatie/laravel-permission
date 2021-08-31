---
title: Exceptions
weight: 3
---

If you need to override exceptions thrown by this package, you can simply use normal [Laravel practices for handling exceptions](https://laravel.com/docs/8.x/errors#rendering-exceptions).

An example is shown below for your convenience, but nothing here is specific to this package other than the name of the exception.

You can find all the exceptions added by this package in the code here: https://github.com/spatie/laravel-permission/tree/main/src/Exceptions


**app/Exceptions/Handler.php**
```php

public function register()
{
    $this->renderable(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, $request) {
        return response()->json([
            'responseMessage' => 'You do not have the required authorization.',
            'responseStatus'  => 403,
        ]);
    });
}
```
