<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;
use Illuminate\Support\Collection;

class GuardDoesNotMatch extends InvalidArgumentException
{
    public static function create(string $givenGuard, Collection $expectedGuards)
    {
        return new static(trans('permission::exceptions.guard_does_not_match-create', [
            'expectedGuards' => $expectedGuards->implode(', '),
            'givenGuard' => $givenGuard,
        ]));
    }
}
