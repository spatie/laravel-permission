<?php

namespace Spatie\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use InvalidArgumentException;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Exceptions\RoleAlreadyExists;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Guard;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\RefreshesPermissionCache;

/**
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class Role extends Model implements RoleContract
{
    use HasPermissions;
    use RefreshesPermissionCache;

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        $attributes['guard_name'] ??= Guard::getDefaultName(static::class);

        parent::__construct($attributes);

        $this->guarded[] = $this->primaryKey;
        $this->table = config('permission.table_names.roles') ?: parent::getTable();
    }

    /**
     * Boot the model and add validation on model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->validateAttributes();
        });
    }

    /**
     * Validate role attributes to prevent security issues.
     *
     * @throws InvalidArgumentException
     */
    protected function validateAttributes(): void
    {
        // Validate name field
        if (isset($this->attributes['name'])) {
            $name = $this->attributes['name'];

            // Check if name is a string
            if (! is_string($name)) {
                throw new InvalidArgumentException('Role name must be a string.');
            }

            // Trim and check for empty name
            $name = trim($name);
            if (empty($name)) {
                throw new InvalidArgumentException('Role name cannot be empty.');
            }

            // Check name length (prevent excessively long names)
            if (strlen($name) > 255) {
                throw new InvalidArgumentException('Role name cannot exceed 255 characters.');
            }

            // Sanitize name - remove control characters and null bytes
            $sanitized = preg_replace('/[\x00-\x1F\x7F]/u', '', $name);

            if ($sanitized !== $name) {
                throw new InvalidArgumentException('Role name contains invalid characters.');
            }

            // Store the trimmed name
            $this->attributes['name'] = $name;
        }

        // Validate guard_name field
        if (isset($this->attributes['guard_name'])) {
            $guardName = $this->attributes['guard_name'];

            if (! is_string($guardName)) {
                throw new InvalidArgumentException('Guard name must be a string.');
            }

            $guardName = trim($guardName);
            if (empty($guardName)) {
                throw new InvalidArgumentException('Guard name cannot be empty.');
            }

            if (strlen($guardName) > 255) {
                throw new InvalidArgumentException('Guard name cannot exceed 255 characters.');
            }

            // Validate guard name format (alphanumeric, dash, underscore only)
            if (! preg_match('/^[a-zA-Z0-9_-]+$/', $guardName)) {
                throw new InvalidArgumentException('Guard name must contain only alphanumeric characters, dashes, and underscores.');
            }

            $this->attributes['guard_name'] = $guardName;
        }
    }

    /**
     * @return RoleContract|Role
     *
     * @throws RoleAlreadyExists
     */
    public static function create(array $attributes = [])
    {
        $attributes['guard_name'] ??= Guard::getDefaultName(static::class);

        $registrar = app(PermissionRegistrar::class);
        $params = ['name' => $attributes['name'], 'guard_name' => $attributes['guard_name']];

        if ($registrar->teams) {
            $teamsKey = $registrar->teamsKey;

            if (array_key_exists($teamsKey, $attributes)) {
                $params[$teamsKey] = $attributes[$teamsKey];
            } else {
                $attributes[$teamsKey] = getPermissionsTeamId();
            }
        }

        if (static::findByParam($params)) {
            throw RoleAlreadyExists::create($attributes['name'], $attributes['guard_name']);
        }

        return static::query()->create($attributes);
    }

    /**
     * A role may be given various permissions.
     */
    public function permissions(): BelongsToMany
    {
        $registrar = app(PermissionRegistrar::class);

        return $this->belongsToMany(
            config('permission.models.permission'),
            config('permission.table_names.role_has_permissions'),
            $registrar->pivotRole,
            $registrar->pivotPermission
        );
    }

    /**
     * A role belongs to some users of the model associated with its guard.
     */
    public function users(): BelongsToMany
    {
        $registrar = app(PermissionRegistrar::class);

        return $this->morphedByMany(
            getModelForGuard($this->attributes['guard_name'] ?? config('auth.defaults.guard')),
            'model',
            config('permission.table_names.model_has_roles'),
            $registrar->pivotRole,
            config('permission.column_names.model_morph_key')
        );
    }

    /**
     * Find a role by its name and guard name.
     *
     * @return RoleContract|Role
     *
     * @throws RoleDoesNotExist
     */
    public static function findByName(string $name, ?string $guardName = null): RoleContract
    {
        $guardName ??= Guard::getDefaultName(static::class);

        $role = static::findByParam(['name' => $name, 'guard_name' => $guardName]);

        if (! $role) {
            throw RoleDoesNotExist::named($name, $guardName);
        }

        return $role;
    }

    /**
     * Find a role by its id (and optionally guardName).
     *
     * @return RoleContract|Role
     */
    public static function findById(int|string $id, ?string $guardName = null): RoleContract
    {
        $guardName ??= Guard::getDefaultName(static::class);

        $role = static::findByParam([(new static)->getKeyName() => $id, 'guard_name' => $guardName]);

        if (! $role) {
            throw RoleDoesNotExist::withId($id, $guardName);
        }

        return $role;
    }

    /**
     * Find or create role by its name (and optionally guardName).
     *
     * @return RoleContract|Role
     */
    public static function findOrCreate(string $name, ?string $guardName = null): RoleContract
    {
        $guardName ??= Guard::getDefaultName(static::class);

        $role = static::findByParam(['name' => $name, 'guard_name' => $guardName]);

        if (! $role) {
            $registrar = app(PermissionRegistrar::class);
            $attributes = ['name' => $name, 'guard_name' => $guardName];

            if ($registrar->teams) {
                $attributes[$registrar->teamsKey] = getPermissionsTeamId();
            }

            return static::query()->create($attributes);
        }

        return $role;
    }

    /**
     * Finds a role based on an array of parameters.
     *
     * @return RoleContract|Role|null
     */
    protected static function findByParam(array $params = []): ?RoleContract
    {
        $query = static::query();
        $registrar = app(PermissionRegistrar::class);

        if ($registrar->teams) {
            $teamsKey = $registrar->teamsKey;

            $query->where(fn ($q) => $q->whereNull($teamsKey)
                ->orWhere($teamsKey, $params[$teamsKey] ?? getPermissionsTeamId())
            );
            unset($params[$teamsKey]);
        }

        foreach ($params as $key => $value) {
            $query->where($key, $value);
        }

        return $query->first();
    }

    /**
     * Determine if the role may perform the given permission.
     *
     * @param  string|int|\Spatie\Permission\Contracts\Permission|\BackedEnum  $permission
     *
     * @throws PermissionDoesNotExist|GuardDoesNotMatch
     */
    public function hasPermissionTo($permission, ?string $guardName = null): bool
    {
        if ($this->getWildcardClass()) {
            return $this->hasWildcardPermission($permission, $guardName);
        }

        $permission = $this->filterPermission($permission, $guardName);

        if (! $this->getGuardNames()->contains($permission->guard_name)) {
            throw GuardDoesNotMatch::create($permission->guard_name, $guardName ? collect([$guardName]) : $this->getGuardNames());
        }

        return $this->loadMissing('permissions')->permissions
            ->contains($permission->getKeyName(), $permission->getKey());
    }
}
