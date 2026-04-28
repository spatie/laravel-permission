<?php

namespace Spatie\Permission\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Support\Config;

trait HasAssignedModels
{
    /**
     * Assign this role to the given models without removing existing assignments.
     *
     * @param  Model|int|string|array<int, Model|int|string>|Collection<int, Model|int|string>  $models
     * @return $this
     */
    public function assignToModels(array|Collection|Model|int|string $models, ?string $modelClass = null): static
    {
        if (! $this->exists) {
            return $this;
        }

        $teamPivot = $this->teamPivot();

        foreach ($this->groupModelsByMorphClass($models, $modelClass) as $morphClass => $ids) {
            $relation = $this->relationForModel($morphClass);
            $existingIds = $relation->pluck(Config::morphKey())->all();

            $relation->attach(array_diff($ids, $existingIds), $teamPivot);
        }

        $this->unsetRelation('users');

        return $this;
    }

    /**
     * Remove this role from the given models.
     *
     * @param  Model|int|string|array<int, Model|int|string>|Collection<int, Model|int|string>  $models
     * @return $this
     */
    public function removeFromModels(array|Collection|Model|int|string $models, ?string $modelClass = null): static
    {
        foreach ($this->groupModelsByMorphClass($models, $modelClass) as $morphClass => $ids) {
            $this->relationForModel($morphClass)->detach($ids);
        }

        $this->unsetRelation('users');

        return $this;
    }

    /**
     * Remove all current model associations and set the given ones.
     *
     * @param  Model|int|string|array<int, Model|int|string>|Collection<int, Model|int|string>  $models
     * @return $this
     */
    public function syncModels(array|Collection|Model|int|string $models, ?string $modelClass = null): static
    {
        if ($this->exists) {
            $this->newPivotQueryForRole()->delete();
        }

        $teamPivot = $this->teamPivot();

        foreach ($this->groupModelsByMorphClass($models, $modelClass) as $morphClass => $ids) {
            $this->relationForModel($morphClass)->attach($ids, $teamPivot);
        }

        $this->unsetRelation('users');

        return $this;
    }

    /**
     * Build a morphedByMany relation pointing to a specific model class.
     */
    protected function relationForModel(string $modelClass): MorphToMany
    {
        return $this->morphedByMany(
            $modelClass,
            'model',
            Config::modelHasRolesTable(),
            app(PermissionRegistrar::class)->pivotRole,
            Config::morphKey(),
        );
    }

    /**
     * Group the given models by class, deduplicating IDs within each class.
     *
     * @param  Model|int|string|array<int, Model|int|string>|Collection<int, Model|int|string>  $models
     * @return array<class-string, list<int|string>>
     */
    private function groupModelsByMorphClass(
        array|Collection|Model|int|string $models,
        ?string $modelClass,
    ): array {
        $defaultModelClass = $this->resolveDefaultModelClass($modelClass);

        return collect(Arr::flatten(Arr::wrap($models)))
            ->reject(fn ($value) => $value === null || $value === '')
            ->reduce(function (array $grouped, $value) use ($defaultModelClass) {
                $class = $value instanceof Model ? $value::class : $defaultModelClass;
                $id = $value instanceof Model ? $value->getKey() : $value;

                if (! in_array($id, $grouped[$class] ?? [], strict: true)) {
                    $grouped[$class][] = $id;
                }

                return $grouped;
            }, []);
    }

    /**
     * Resolve the model class to use when raw IDs are passed.
     */
    private function resolveDefaultModelClass(?string $modelClass): string
    {
        return $modelClass
            ?? config('permission.models.default_model')
            ?? getModelForGuard($this->attributes['guard_name'] ?? config('auth.defaults.guard'));
    }

    /**
     * @return array<string, int|string|null>
     */
    private function teamPivot(): array
    {
        if (! Config::teamsEnabled()) {
            return [];
        }

        return [Config::teamForeignKey() => getPermissionsTeamId()];
    }

    private function newPivotQueryForRole(): Builder
    {
        return $this->getConnection()
            ->table(Config::modelHasRolesTable())
            ->where(app(PermissionRegistrar::class)->pivotRole, $this->getKey());
    }
}
