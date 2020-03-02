<?php

namespace Spatie\Permission;

use Illuminate\Support\Collection;
use Spatie\Permission\Exceptions\WildcardPermissionNotProperlyFormatted;

class WildcardPermission
{
    /** @var string */
    const WILDCARD_TOKEN = '*';

    /** @var string */
    const PART_DELIMITER = '.';

    /** @var string */
    const SUBPART_DELIMITER = ',';

    /** @var string */
    protected $permission;

    /** @var Collection */
    protected $parts;

    /**
     * @param string $permission
     */
    public function __construct(string $permission)
    {
        $this->permission = $permission;
        $this->parts = collect();

        $this->setParts();
    }

    /**
     * @param string|WildcardPermission $permission
     *
     * @return bool
     */
    public function implies($permission): bool
    {
        if (is_string($permission)) {
            $permission = new self($permission);
        }

        $otherParts = $permission->getParts();

        $i = 0;
        foreach ($otherParts as $otherPart) {
            if ($this->getParts()->count() - 1 < $i) {
                return true;
            }

            if (! $this->parts->get($i)->contains(self::WILDCARD_TOKEN)
                && ! $this->containsAll($this->parts->get($i), $otherPart)) {
                return false;
            }

            $i++;
        }

        for ($i; $i < $this->parts->count(); $i++) {
            if (! $this->parts->get($i)->contains(self::WILDCARD_TOKEN)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Collection $part
     * @param Collection $otherPart
     *
     * @return bool
     */
    protected function containsAll(Collection $part, Collection $otherPart): bool
    {
        foreach ($otherPart->toArray() as $item) {
            if (! $part->contains($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return Collection
     */
    public function getParts(): Collection
    {
        return $this->parts;
    }

    /**
     * Sets the different parts and subparts from permission string.
     *
     * @return void
     */
    protected function setParts(): void
    {
        if (empty($this->permission) || $this->permission == null) {
            throw WildcardPermissionNotProperlyFormatted::create($this->permission);
        }

        $parts = collect(explode(self::PART_DELIMITER, $this->permission));

        $parts->each(function ($item, $key) {
            $subParts = collect(explode(self::SUBPART_DELIMITER, $item));

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
