<?php

namespace Spatie\Permission\Tests\TestModels;

class Manager extends User
{
    // this function is added here to support the unit tests verifying it works
    // When present, it takes precedence over the $guard_name property.
    public function guardName(): string
    {
        return 'jwt';
    }

    // intentionally different property value for the sake of unit tests
    protected string $guard_name = 'web';
}
