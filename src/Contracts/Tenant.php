<?php

namespace Spatie\Permission\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface Tenant
{
    /**
     * A tenant may have various user access the application.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany;

    /**
     * A tenant may be given various roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): BelongsToMany;

    /**
     * Find a tenant by its primary key.
     *
     * @param string|int $id
     *
     * @return \Spatie\Permission\Contracts\Tenant
     *
     * @throws \Spatie\Permission\Exceptions\TenantDoesNotExist
     */
    public static function findById($id): self;
}
