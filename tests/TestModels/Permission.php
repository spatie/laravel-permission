<?php

namespace Spatie\Permission\Tests\TestModels;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Permission extends \Spatie\Permission\Models\Permission
{
    use SoftDeletes;

    protected $primaryKey = 'permission_test_id';

    protected $visible = [
        'permission_test_id',
        'name',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(static function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }
}
