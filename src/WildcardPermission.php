<?php

namespace Spatie\Permission;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Permission\Contracts\Wildcard;
use Spatie\Permission\Exceptions\WildcardPermissionNotProperlyFormatted;

class WildcardPermission implements Wildcard
{
    /** @var string */
    public const WILDCARD_TOKEN = '*';

    /** @var non-empty-string */
    public const PART_DELIMITER = '.';

    /** @var non-empty-string */
    public const SUBPART_DELIMITER = ',';

    protected Model $record;

    public function __construct(Model $record)
    {
        $this->record = $record;
    }

    public function getIndex(): array
    {
        $index = [];

        foreach ($this->record->getAllPermissions() as $permission) {
            $index[$permission->guard_name] = $this->buildIndex(
                $index[$permission->guard_name] ?? [],
                explode(static::PART_DELIMITER, $permission->name),
                $permission->name,
            );
        }

        return $index;
    }

    protected function buildIndex(array $index, array $parts, string $permission): array
    {
        if (empty($parts)) {
            $index[null] = true;

            return $index;
        }

        $part = array_shift($parts);

        if (blank($part)) {
            throw WildcardPermissionNotProperlyFormatted::create($permission);
        }

        if (! Str::contains($part, static::SUBPART_DELIMITER)) {
            $index[$part] = $this->buildIndex(
                $index[$part] ?? [],
                $parts,
                $permission,
            );
        }

        $subParts = explode(static::SUBPART_DELIMITER, $part);

        foreach ($subParts as $subPart) {
            if (blank($subPart)) {
                throw WildcardPermissionNotProperlyFormatted::create($permission);
            }

            $index[$subPart] = $this->buildIndex(
                $index[$subPart] ?? [],
                $parts,
                $permission,
            );
        }

        return $index;
    }

    public function implies(string $permission, string $guardName, array $index): bool
    {
        if (! array_key_exists($guardName, $index)) {
            return false;
        }

        $permission = explode(static::PART_DELIMITER, $permission);

        return $this->checkIndex($permission, $index[$guardName]);
    }

    protected function checkIndex(array $permission, array $index): bool
    {
        if (array_key_exists(strval(null), $index)) {
            return true;
        }

        if (empty($permission)) {
            return false;
        }

        $firstPermission = array_shift($permission);

        if (
            array_key_exists($firstPermission, $index) &&
            $this->checkIndex($permission, $index[$firstPermission])
        ) {
            return true;
        }

        if (array_key_exists(static::WILDCARD_TOKEN, $index)) {
            return $this->checkIndex($permission, $index[static::WILDCARD_TOKEN]);
        }

        return false;
    }
}
