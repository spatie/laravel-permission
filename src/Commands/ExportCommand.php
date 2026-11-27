<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Spatie\Permission\PermissionRegistrar;

class ExportCommand extends Command
{
    protected $signature = 'permission:export
            {--path= : The file to write the JSON to (default: resources/js/permissions.json)}
            {--guard= : Only export permissions and roles for this guard}
            {--only= : Only export "permissions" or "roles"}';

    protected $description = 'Export permission and role names to a JSON file';

    public function handle(): int
    {
        $only = $this->option('only');

        if ($only !== null && ! in_array($only, ['permissions', 'roles'], true)) {
            $this->error('The --only option must be either "permissions" or "roles".');

            return self::FAILURE;
        }

        $guard = $this->option('guard');
        $permissionRegistrar = app(PermissionRegistrar::class);

        $export = [];

        if ($only !== 'roles') {
            $export['permissions'] = $this->names($permissionRegistrar->getPermissionClass(), $guard);
        }

        if ($only !== 'permissions') {
            $export['roles'] = $this->names($permissionRegistrar->getRoleClass(), $guard);
        }

        $path = $this->option('path') ?: resource_path('js/permissions.json');

        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        $this->info("Permissions exported to [{$path}].");

        return self::SUCCESS;
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return array<int, string>
     */
    protected function names(string $modelClass, ?string $guard): array
    {
        return $modelClass::query()
            ->when($guard, fn ($query) => $query->where('guard_name', $guard))
            ->pluck('name')
            ->unique()
            ->sort(SORT_STRING)
            ->values()
            ->all();
    }
}
