<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class InvalidArgument extends InvalidArgumentException
{
    public static function guardDoesNotMatch(string $givenGuard, string $expectedGuard)
    {
        return new static("The given role or permission should use guard `{$expectedGuard}` instead of `{$givenGuard}`.");
    }
}
