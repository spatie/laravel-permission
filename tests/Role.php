<?php

namespace Spatie\Permission\Test;

class Role extends \Spatie\Permission\Models\Role
{
    protected $primaryKey = 'role_test_id';
    protected $guarded = ['role_test_id'];

    protected $visible = [
      'role_test_id',
      'name',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = \Str::uuid()->toString();
            }
        });
    }

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }
}
