<?php

namespace Spatie\Permission\Test;

class Permission extends \Spatie\Permission\Models\Permission
{
    protected $primaryKey = 'permission_test_id';
    
    protected $visible = [
      'permission_test_id',
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
