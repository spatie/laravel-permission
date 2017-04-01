<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class GuardDoesNotMatch extends InvalidArgumentException
{
    public static function create(string $givenGuard, string $expectedGuard)
    {
        return new static("The given role or permission should use guard `{$expectedGuard}` instead of `{$givenGuard}`.");
    }
}
