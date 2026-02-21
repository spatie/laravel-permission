<?php

namespace Spatie\Permission\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
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
     * Returns a deduplicated Collection of Model instances.
     *
     * @param  Model|Model[]|Collection  ...$models
     */
    private function collectModels(...$models): Collection
    {
        return collect($models)
            ->flatten()
            ->reduce(function (Collection $carry, $model) {
                if ($model === null || $model === '') {
                    return $carry;
                }

                $key = $model->getMorphClass().':'.$model->getKey();

                if (! $carry->has($key)) {
                    $carry->put($key, $model);
                }

                return $carry;
            }, collect())
            ->values();
    }

    /**
     * Remove all current model associations and set the given ones.
     *
     * @param  Model|Model[]|Collection  ...$models
     * @return $this
     */
    public function syncModels(...$models): static
    {
        $models = $this->collectModels($models);

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

        foreach ($models->groupBy(fn (Model $m) => $m::class) as $class => $group) {
            $this->morphRelationForModelClass($class)
                ->attach($group->pluck((new $class)->getKeyName())->toArray(), $teamPivot);
        }

        $this->unsetRelation('users');

        return $this;
    }
}
