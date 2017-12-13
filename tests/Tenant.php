<?php

namespace Spatie\Permission\Test;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Exceptions\TenantAlreadyExist;
use Spatie\Permission\Traits\RefreshesPermissionCache;
use Spatie\Permission\Contracts\Tenant as TenantContract;
use Spatie\Permission\Traits\TenantBase;

class Tenant extends Model implements TenantContract
{
    use TenantBase;
    use RefreshesPermissionCache;

    public $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('permission.table_names.tenants'));
        $this->setKeyName(config('permission.foreign_keys.tenants.id'));
        $this->setKeyType(config('permission.foreign_keys.tenants.key_type'));
    }

    public static function create(array $attributes = [])
    {
        $id = config('permission.foreign_keys.tenants.id');

        if (! empty($attributes[$id]) && static::where($id, $attributes[$id])->first()) {
            throw TenantAlreadyExist::create($attributes[$id]);
        }

        return static::query()->create($attributes);
    }
}