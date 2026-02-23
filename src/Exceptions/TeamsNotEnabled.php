<?php

namespace Spatie\Permission\Exceptions;

use BadMethodCallException;

class TeamsNotEnabled extends BadMethodCallException
{
    public static function create(): static
    {
        return new static(__('The teams feature is not enabled. Set `teams` to `true` in your permission config file.'));
    }
}
