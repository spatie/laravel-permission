<?php

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

it('measures lookup time and retained memory', function () {
    $count = (int) (getenv('BENCH_COUNT') ?: 300);
    $rows = [];
    for ($index = 0; $index < $count; $index++) {
        $rows[] = ['name' => "bench-{$index}", 'guard_name' => 'web'];
    }
    foreach (array_chunk($rows, 300) as $chunk) {
        DB::table('permissions')->insert($chunk);
    }
    unset($rows, $chunk);
    $registrar = app(PermissionRegistrar::class);
    $registrar->forgetCachedPermissions();
    $miss = static function (): void {
        try {
            Permission::findByName('policy-miss', 'web');
        } catch (PermissionDoesNotExist) {
        }
    };
    $miss();
    $hydrationTimes = [];
    for ($batch = 0; $batch < 5; $batch++) {
        $registrar->clearPermissionsCollection();
        gc_collect_cycles();
        $before = memory_get_usage();
        memory_reset_peak_usage();
        $start = hrtime(true);
        $miss();
        $hydrationTimes[] = (hrtime(true) - $start) / 1000;
        $loadedMemory = memory_get_usage() - $before;
        $hydrationPeak = memory_get_peak_usage() - $before;
    }
    sort($hydrationTimes);

    $scenarios = [
        'first' => static fn ($iteration) => Permission::findByName('edit-articles', 'web'),
        'last' => static fn ($iteration) => Permission::findByName('bench-'.($count - 1), 'web'),
        'miss' => static fn ($iteration) => $miss(),
        'id-last' => static fn ($iteration) => Permission::findById($count + 5, 'web'),
        'mixed-20' => static fn ($iteration) => Permission::findByName('bench-'.($count - 1 - $iteration % 20), 'web'),
        'all-exposed-first' => static fn ($iteration) => Permission::findByName('edit-articles', 'web'),
        'all-exposed-miss' => static fn ($iteration) => $miss(),
    ];
    $results = [];
    foreach ($scenarios as $name => $lookup) {
        $times = [];
        for ($batch = 0; $batch < 5; $batch++) {
            $registrar->clearPermissionsCollection();
            $miss();
            if (str_starts_with($name, 'all-exposed')) {
                $registrar->getPermissions();
            }
            for ($iteration = 0; $iteration < 20; $iteration++) {
                $lookup($iteration);
            }
            $start = hrtime(true);
            for ($iteration = 0; $iteration < 500; $iteration++) {
                $lookup($iteration);
            }
            $times[] = (hrtime(true) - $start) / 500 / 1000;
        }
        sort($times);
        $results[$name] = round($times[2], 2);
    }
    $registrar->clearPermissionsCollection();
    gc_collect_cycles();
    $before = memory_get_usage();
    $registrar->getPermissions();
    $exposedMemory = memory_get_usage() - $before;
    $registrar->clearPermissionsCollection();
    gc_collect_cycles();
    $retainedMemory = memory_get_usage() - $before;
    fwrite(STDOUT, json_encode([
        'permissions' => $count + 5,
        'warm_us' => $results,
        'hydration_and_miss_us' => round($hydrationTimes[2], 2),
        'loaded_bytes' => $loadedMemory,
        'hydration_peak_bytes' => $hydrationPeak,
        'all_exposed_bytes' => $exposedMemory,
        'after_clear_bytes' => $retainedMemory,
    ], JSON_PRETTY_PRINT).PHP_EOL);
    expect(Permission::findByName('bench-'.($count - 1))->getKey())->toBe($count + 5);
});
