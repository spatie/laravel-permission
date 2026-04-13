<?php

namespace Spatie\Permission\Exceptions;

use RuntimeException;

class TeamModelNotConfigured extends RuntimeException
{
    public static function create(): static
    {
        return new static(__('No team model configured. Set `models.team` in your permission config file.'));
    }
}
