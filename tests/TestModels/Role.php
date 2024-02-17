<?php

namespace Spatie\Permission\Tests\TestModels;

use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends \Spatie\Permission\Models\Role
{
    use SoftDeletes;

    protected $primaryKey = 'role_test_id';

    protected $visible = [
        'role_test_id',
        'name',
    ];

    const HIERARCHY_TABLE = 'roles_hierarchy';

    /**
     * @return string|\BackedEnum
     */
    public function getNameAttribute()
    {
        $name = $this->attributes['name'];

        if (str_contains($name, 'casted_enum')) {
            return TestRolePermissionsEnum::from($name);
        }

        return $name;
    }

    /**
     * @return BelongsToMany
     */
    public function parents()
    {
        return $this->belongsToMany(
            static::class,
            static::HIERARCHY_TABLE,
            'child_id',
            'parent_id');
    }

    /**
     * @return BelongsToMany
     */
    public function children()
    {
        return $this->belongsToMany(
            static::class,
            static::HIERARCHY_TABLE,
            'parent_id',
            'child_id');
    }

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
