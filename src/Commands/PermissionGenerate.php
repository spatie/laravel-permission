<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\PermissionRegistrar;

class PermissionGenerate extends Command
{
    protected $signature = 'permission:generate
        {privilegedRole? : Override the configured privileged role for every guard}
        {--guard= : Generate permissions for one configured guard only}';

    protected $description = 'Generate configured roles and permissions';

    public function handle(PermissionRegistrar $permissionRegistrar): int
    {
        $definitions = collect(config('permission.defined_permissions', config('permission.cache.defined_permissions', [])));

        if ($definitions->isEmpty()) {
            $this->warn('No permissions configured at `permission.defined_permissions`.');

            return self::SUCCESS;
        }

        if ($guard = $this->option('guard')) {
            $definitions = $definitions->only($guard);

            if ($definitions->isEmpty()) {
                $this->warn("No permissions configured for guard `{$guard}`.");

                return self::SUCCESS;
            }
        }

        $roleClass = get_class(app(RoleContract::class));
        $permissionClass = get_class(app(PermissionContract::class));
        $generatedPermissions = 0;

        $definitions->each(function (array $definition, string $guardName) use ($roleClass, $permissionClass, &$generatedPermissions) {
            $roleName = $this->argument('privilegedRole') ?: ($definition['privileged_role'] ?? null);

            if (blank($roleName)) {
                $this->warn("Skipping guard `{$guardName}` because it has no privileged role configured.");

                return;
            }

            $role = $roleClass::query()->updateOrCreate([
                'name' => $roleName,
                'guard_name' => $guardName,
            ]);

            $prefix = $definition['permission_prefix'] ?? '';
            $permissionGroups = $definition['permissions'] ?? $definition['permission'] ?? [];

            foreach ($permissionGroups as $group => $permissions) {
                foreach ((array) $permissions as $configuredPermission) {
                    $permissionName = $prefix.$configuredPermission;

                    $permission = $permissionClass::query()->updateOrCreate(
                        [
                            'name' => $permissionName,
                            'guard_name' => $guardName,
                        ],
                        [
                            'display_name' => $this->displayName($permissionName),
                            'permission_group' => $group,
                        ]
                    );

                    $role->givePermissionTo($permission);
                    $generatedPermissions++;
                }
            }

            $this->line("Generated permissions for `{$roleName}` on guard `{$guardName}`.");
        });

        $permissionRegistrar->forgetCachedPermissions();

        $this->info("Permissions generated successfully. {$generatedPermissions} permission(s) processed.");

        return self::SUCCESS;
    }

    protected function displayName(string $permission): string
    {
        return Str::of($permission)
            ->replace(['_', '-'], ' ')
            ->title()
            ->toString();
    }
}
