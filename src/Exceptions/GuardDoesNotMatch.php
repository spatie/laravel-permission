<?php

namespace Spatie\Permission\Exceptions;

use Illuminate\Support\Collection;
use InvalidArgumentException;

class GuardDoesNotMatch extends InvalidArgumentException
{
    public static function create(string $givenGuard, Collection $expectedGuards): static
    {
        return new static(__('The given role or permission should use guard `:expected` instead of `:given`.', [
            'expected' => $expectedGuards->implode(', '),
            'given' => $givenGuard,
        ]));
    }
}
