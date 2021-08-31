<?php

namespace Spatie\Permission\Traits;

use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\PermissionRegistrar;

trait HasPermissionsCache
{
    /** @var int */
    protected $permissionsTeamId = null;
    protected $rolesTeamId = null;
    
    protected function getPermissionCacheKey(string $relation): string
    {
        $teams = PermissionRegistrar::$teams;
        $teamPropertie = $relation . 'TeamId';
        $this->$teamPropertie = $teams ? app(PermissionRegistrar::class)->getPermissionsTeamId() : null;
        return sprintf(PermissionRegistrar::$cacheKey.'.'.str_replace('\\', '.', $this->getMorphClass()).($teams ? '.%d-%d-' : '.%d-').$relation, $this->getKey(), $this->$teamPropertie);
    }
    private function getCachedPermissions(string $relation): Collection
    {
        if ($this->relationLoaded($relation)) {
            $teamPropertie = $relation . 'TeamId';
            if ($this->$teamPropertie != app(PermissionRegistrar::class)->getPermissionsTeamId()) {
                $this->loadCachedRelation($relation);
            }

            return $this->getRelationValue($relation);
        }

        return $this->loadCachedRelation($relation);
    }
    public function loadCachedRelation(string $relation): Collection
    {
        if (! in_array($relation, ['roles', 'permissions'])) {
            return new Collection();
        }
        $cacheRepository = app(PermissionRegistrar::class)->getCacheRepository();
        $cacheIsTaggable = $cacheRepository->getStore() instanceof \Illuminate\Cache\TaggableStore;
        $cache = $cacheIsTaggable ? $cacheRepository->tags(['permissions'.str_replace('\\', '.', $this->getMorphClass())]) : $cacheRepository;

        $array = $cache->remember($this->getPermissionCacheKey($relation), config('session.lifetime', 120)* 60, function () use ($relation) {
            return $this->$relation()->get()->map(function ($data) {
                return ['i' => $data->getKey(), 'n' => $data->name, 'g' => $data->guard_name];
            })->all();
        });

        $class = $relation === 'roles' ? $this->getRoleClass() : $this->getPermissionClass();
        $keyName = (new $class)->getKeyName();
        $collection = $class::hydrate(
            collect($array)
            ->map(function ($item) use ($keyName) {
                return [$keyName => $item['i'], 'name' => $item['n'], 'guard_name' => $item['g']];
            })->all()
        );
        $this->setRelation($relation, $collection);

        return $collection;
    }
    public function getRolesAttribute(): Collection
    {
        return $this->getCachedPermissions('roles');
    }
    public function getPermissionsAttribute(): Collection
    {
        return $this->getCachedPermissions('permissions');
    }
    private function forgetModelCachedRelation(string $relation)
    {
        $cacheRepository = app(PermissionRegistrar::class)->getCacheRepository();
        $cacheIsTaggable = $cacheRepository->getStore() instanceof \Illuminate\Cache\TaggableStore;
        $cache = $cacheIsTaggable ? $cacheRepository->tags(['permissions'.str_replace('\\', '.', $this->getMorphClass())]) : $cacheRepository;
        $this->unsetRelation($relation);
        $cache->forget($this->getPermissionCacheKey($relation));
    }
    public function forgetModelCachedRelations()
    {
        $this->forgetModelCachedRoles();
        $this->forgetModelCachedPermissions();

        return $this;
    }
    public function forgetModelCachedRoles()
    {
        $this->forgetModelCachedRelation('roles');

        return $this;
    }
    public function forgetModelCachedPermissions()
    {
        $this->forgetModelCachedRelation('permissions');

        return $this;
    }
    public static function forgetAllModelCachedPermissions()
    {
        $cache = app(PermissionRegistrar::class)->getCacheRepository();
        $cacheIsTaggable = $cache->getStore() instanceof \Illuminate\Cache\TaggableStore;
        if ($cacheIsTaggable) {
            $cache->tags(['permissions'.str_replace('\\', '.', $this->getMorphClass())])->flush();
        } else {
            static::select((new static)->getKeyName())->get()->each(function ($model) use ($cache) {
                $cache->forget($model->getPermissionCacheKey('roles'));
                $cache->forget($model->getPermissionCacheKey('permissions'));
            });
        }
    }
}
