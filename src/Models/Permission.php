<?php

namespace Spatie\Permission\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use InvalidArgumentException;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Exceptions\PermissionAlreadyExists;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Guard;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\RefreshesPermissionCache;

/**
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class Permission extends Model implements PermissionContract
{
    use HasRoles;
    use RefreshesPermissionCache;

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        $attributes['guard_name'] ??= Guard::getDefaultName(static::class);

        parent::__construct($attributes);

        $this->guarded[] = $this->primaryKey;
        $this->table = config('permission.table_names.permissions') ?: parent::getTable();
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
     * Validate permission attributes to prevent security issues.
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
                throw new InvalidArgumentException('Permission name must be a string.');
            }

            // Trim and check for empty name
            $name = trim($name);
            if (empty($name)) {
                throw new InvalidArgumentException('Permission name cannot be empty.');
            }

            // Check name length (prevent excessively long names)
            if (strlen($name) > 255) {
                throw new InvalidArgumentException('Permission name cannot exceed 255 characters.');
            }

            // Sanitize name - remove control characters and null bytes
            $sanitized = preg_replace('/[\x00-\x1F\x7F]/u', '', $name);

            if ($sanitized !== $name) {
                throw new InvalidArgumentException('Permission name contains invalid characters.');
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
     * @return PermissionContract|Permission
     *
     * @throws PermissionAlreadyExists
     */
    public static function create(array $attributes = [])
    {
        $attributes['guard_name'] ??= Guard::getDefaultName(static::class);

        $permission = static::getPermission(['name' => $attributes['name'], 'guard_name' => $attributes['guard_name']]);

        if ($permission) {
            throw PermissionAlreadyExists::create($attributes['name'], $attributes['guard_name']);
        }

        return static::query()->create($attributes);
    }

    /**
     * A permission can be applied to roles.
     */
    public function roles(): BelongsToMany
    {
        $registrar = app(PermissionRegistrar::class);

        return $this->belongsToMany(
            config('permission.models.role'),
            config('permission.table_names.role_has_permissions'),
            $registrar->pivotPermission,
            $registrar->pivotRole
        );
    }

    /**
     * A permission belongs to some users of the model associated with its guard.
     */
    public function users(): BelongsToMany
    {
        $registrar = app(PermissionRegistrar::class);

        return $this->morphedByMany(
            getModelForGuard($this->attributes['guard_name'] ?? config('auth.defaults.guard')),
            'model',
            config('permission.table_names.model_has_permissions'),
            $registrar->pivotPermission,
            config('permission.column_names.model_morph_key')
        );
    }

    /**
     * Find a permission by its name (and optionally guardName).
     *
     * @return PermissionContract|Permission
     *
     * @throws PermissionDoesNotExist
     */
    public static function findByName(string $name, ?string $guardName = null): PermissionContract
    {
        $guardName ??= Guard::getDefaultName(static::class);
        $permission = static::getPermission(['name' => $name, 'guard_name' => $guardName]);
        if (! $permission) {
            throw PermissionDoesNotExist::create($name, $guardName);
        }

        return $permission;
    }

    /**
     * Find a permission by its id (and optionally guardName).
     *
     * @return PermissionContract|Permission
     *
     * @throws PermissionDoesNotExist
     */
    public static function findById(int|string $id, ?string $guardName = null): PermissionContract
    {
        $guardName ??= Guard::getDefaultName(static::class);
        $permission = static::getPermission([(new static)->getKeyName() => $id, 'guard_name' => $guardName]);

        if (! $permission) {
            throw PermissionDoesNotExist::withId($id, $guardName);
        }

        return $permission;
    }

    /**
     * Find or create permission by its name (and optionally guardName).
     *
     * @return PermissionContract|Permission
     */
    public static function findOrCreate(string $name, ?string $guardName = null): PermissionContract
    {
        $guardName ??= Guard::getDefaultName(static::class);
        $permission = static::getPermission(['name' => $name, 'guard_name' => $guardName]);

        if (! $permission) {
            return static::query()->create(['name' => $name, 'guard_name' => $guardName]);
        }

        return $permission;
    }

    /**
     * Get the current cached permissions.
     */
    protected static function getPermissions(array $params = [], bool $onlyOne = false): Collection
    {
        return app(PermissionRegistrar::class)
            ->setPermissionClass(static::class)
            ->getPermissions($params, $onlyOne);
    }

    /**
     * Get the current cached first permission.
     *
     * @return PermissionContract|Permission|null
     */
    protected static function getPermission(array $params = []): ?PermissionContract
    {
        /** @var PermissionContract|null */
        return static::getPermissions($params, true)->first();
    }
}
