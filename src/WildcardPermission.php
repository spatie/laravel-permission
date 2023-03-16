<?php

namespace Spatie\Permission;

use Illuminate\Support\Collection;
use Spatie\Permission\Contracts\Wildcard;
use Spatie\Permission\Exceptions\WildcardPermissionNotProperlyFormatted;

class WildcardPermission implements Wildcard
{
    /** @var string */
    public const WILDCARD_TOKEN = '*';

    /** @var string */
    public const PART_DELIMITER = '.';

    /** @var string */
    public const SUBPART_DELIMITER = ',';

    /** @var string */
    protected $permission;

    /** @var Collection */
    protected $parts;

    public function __construct(string $permission)
    {
        $this->permission = $permission;
        $this->parts = collect();

        $this->setParts();
    }

    /**
     * @param  string|WildcardPermission  $permission
     */
    public function implies($permission): bool
    {
        if (is_string($permission)) {
            $permission = new static($permission);
        }

        $otherParts = $permission->getParts();

        $i = 0;
        $partsCount = $this->getParts()->count();
        foreach ($otherParts as $otherPart) {
            if ($partsCount - 1 < $i) {
                return true;
            }

            if (! $this->parts->get($i)->contains(static::WILDCARD_TOKEN)
                && ! $this->containsAll($this->parts->get($i), $otherPart)) {
                return false;
            }

            $i++;
        }

        for ($i; $i < $partsCount; $i++) {
            if (! $this->parts->get($i)->contains(static::WILDCARD_TOKEN)) {
                return false;
            }
        }

        return true;
    }

    protected function containsAll(Collection $part, Collection $otherPart): bool
    {
        foreach ($otherPart->toArray() as $item) {
            if (! $part->contains($item)) {
                return false;
            }
        }

        return true;
    }

    public function getParts(): Collection
    {
        return $this->parts;
    }

    /**
     * Sets the different parts and subparts from permission string.
     */
    protected function setParts(): void
    {
        if (empty($this->permission) || $this->permission == null) {
            throw WildcardPermissionNotProperlyFormatted::create($this->permission);
        }

        $parts = collect(explode(static::PART_DELIMITER, $this->permission));

        $parts->each(function ($item, $key) {
            $subParts = collect(explode(static::SUBPART_DELIMITER, $item));

            if ($subParts->isEmpty() || $subParts->contains('')) {
                throw WildcardPermissionNotProperlyFormatted::create($this->permission);
            }

            $this->parts->add($subParts);
        });

        if ($this->parts->isEmpty()) {
            throw WildcardPermissionNotProperlyFormatted::create($this->permission);
        }
    }
}
