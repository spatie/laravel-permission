<?php

namespace Spatie\Permission\Tests\TestModels;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Role extends \Spatie\Permission\Models\Role
{
    use SoftDeletes;

    protected $primaryKey = 'role_test_id';

    protected $visible = [
        'role_test_id',
        'name',
    ];

    const HIERARCHY_TABLE = 'roles_hierarchy';

    public function getNameAttribute(): \BackedEnum|string
    {
        $name = $this->attributes['name'];

        if (str_contains($name, 'casted_enum')) {
            return TestRolePermissionsEnum::from($name);
        }

        return $name;
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(
            static::class,
            static::HIERARCHY_TABLE,
            'child_id',
            'parent_id');
    }

    public function children(): BelongsToMany
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
