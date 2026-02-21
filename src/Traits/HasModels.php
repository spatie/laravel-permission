<?php

namespace Spatie\Permission\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Permission\PermissionRegistrar;

trait HasModels
{
    /**
     * Build a morphedByMany relation for a specific model class.
     */
    protected function morphRelationForModelClass(string $modelClass): MorphToMany
    {
        return $this->morphedByMany(
            $modelClass,
            'model',
            config('permission.table_names.model_has_roles'),
            app(PermissionRegistrar::class)->pivotRole,
            config('permission.column_names.model_morph_key')
        );
    }

    /**
     * Returns a grouped array of model IDs keyed by morph class.
     *
     * @param  Model|int|string|array|\Illuminate\Support\Collection  ...$models
     * @return array<string, list<int|string>>
     */
    private function collectModels(...$models): array
    {
        $guardModelClass = getModelForGuard($this->attributes['guard_name'] ?? config('auth.defaults.guard'));

        return collect($models)
            ->flatten()
            ->reduce(function (array $carry, $model) use ($guardModelClass) {
                if ($model === null || $model === '') {
                    return $carry;
                }

                if (! $model instanceof Model) {
                    $model = $guardModelClass::findOrFail($model);
                }

                $morphClass = $model->getMorphClass();
                $id = $model->getKey();

                if (! isset($carry[$morphClass]) || ! in_array($id, $carry[$morphClass])) {
                    $carry[$morphClass][] = $id;
                }

                return $carry;
            }, []);
    }

    /**
     * Attach models to this role.
     *
     * @param  Model|int|string|array|\Illuminate\Support\Collection  ...$models
     * @return $this
     */
    public function attachModels(...$models): static
    {
        $grouped = $this->collectModels($models);

        $teamPivot = app(PermissionRegistrar::class)->teams
            ? [app(PermissionRegistrar::class)->teamsKey => getPermissionsTeamId()]
            : [];

        if ($this->getModel()->exists) {
            foreach ($grouped as $morphClass => $ids) {
                $relation = $this->morphRelationForModelClass($morphClass);
                $currentIds = $relation->pluck(config('permission.column_names.model_morph_key'))->toArray();
                $relation->attach(array_diff($ids, $currentIds), $teamPivot);
            }
        }

        $this->unsetRelation('users');

        return $this;
    }

    /**
     * Detach models from this role.
     *
     * @param  Model|int|string|array|\Illuminate\Support\Collection  ...$models
     * @return $this
     */
    public function detachModels(...$models): static
    {
        $grouped = $this->collectModels($models);

        foreach ($grouped as $morphClass => $ids) {
            $this->morphRelationForModelClass($morphClass)->detach($ids);
        }

        $this->unsetRelation('users');

        return $this;
    }

    /**
     * Remove all current model associations and set the given ones.
     *
     * @param  Model|int|string|array|\Illuminate\Support\Collection  ...$models
     * @return $this
     */
    public function syncModels(...$models): static
    {
        $grouped = $this->collectModels($models);

        if ($this->getModel()->exists) {
            $morphTypes = $this->getConnection()
                ->table(config('permission.table_names.model_has_roles'))
                ->where(app(PermissionRegistrar::class)->pivotRole, $this->getKey())
                ->distinct()
                ->pluck('model_type');

            foreach ($morphTypes as $morphType) {
                $this->morphRelationForModelClass($morphType)->detach();
            }

            $this->unsetRelation('users');
        }

        $teamPivot = app(PermissionRegistrar::class)->teams
            ? [app(PermissionRegistrar::class)->teamsKey => getPermissionsTeamId()]
            : [];

        foreach ($grouped as $morphClass => $ids) {
            $this->morphRelationForModelClass($morphClass)->attach($ids, $teamPivot);
        }

        $this->unsetRelation('users');

        return $this;
    }
}
