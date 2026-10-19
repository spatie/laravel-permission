# Permission lookup benchmark

Run explicitly; this file is not part of the normal `*Test.php` suite:

```sh
CACHE_DRIVER=array vendor/bin/pest tests/Benchmarks/PermissionLookup.php
CACHE_DRIVER=array BENCH_COUNT=3000 vendor/bin/pest tests/Benchmarks/PermissionLookup.php
```

`BENCH_COUNT` is the number of additional permissions (at least 20); the test fixture
already contains five. Use the same PHP binary, Composer lock file, cache driver,
and benchmark file for both revisions. Disable coverage and Xdebug, run revisions
sequentially, and repeat the comparison to check for machine noise.

Warm timings are microseconds per public `findByName()` or `findById()` call:
the median of five batches of 500 calls, after 20 warm-up calls per batch.
Database seeding and cache hydration are outside those batches. Misses include
catching `PermissionDoesNotExist`. `mixed-20` cycles over the last 20 names.
`all-exposed` first calls `getPermissions()` to exercise the cost of tracking all
shared, mutable models, rather than benchmarking only a repeatedly queried name.

Hydration timings include one miss after clearing the in-memory collection, with
the persistent cache already warm, and report the median of five runs. They do
not measure a cold database load. Memory figures use `memory_get_usage(false)`:
loaded collection/index, peak allocation during hydration, all models returned,
and residual allocation after clearing. They exclude the pre-warmed persistent
cache and Laravel application. Peak and retained figures are from the last run.

The index trades hydration work and O(n) memory for faster repeated lookups.
Custom models, keyed collections, numeric comparisons and mutated models retain
the original filter. Checking previously returned models makes the general lookup
O(k), where k is the number of distinct returned models, not unconditionally O(1).
Early hits use the filter when that requires fewer model reads. This benchmark
does not substantiate application-level or Filament page latency claims.
