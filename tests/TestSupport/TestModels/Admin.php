<?php

namespace Spatie\Permission\Tests\TestSupport\TestModels;

class Admin extends User
{
    protected $table = 'admins';

    protected $touches = ['roles', 'permissions'];
}
