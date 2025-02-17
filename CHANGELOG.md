# Changelog

All notable changes to `laravel-permission` will be documented in this file

## 6.15.0 - 2025-02-17

### What's Changed

* Added 4 events for adding and removing roles or permissions by @sven-wegner in https://github.com/spatie/laravel-permission/pull/2742
* Fixed bug of loading user roles of different teams to current team by @mohamedds-12 in https://github.com/spatie/laravel-permission/pull/2803

### New Contributors

* @sven-wegner made their first contribution in https://github.com/spatie/laravel-permission/pull/2742
* @mohamedds-12 made their first contribution in https://github.com/spatie/laravel-permission/pull/2803

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/6.14.0...6.15.0

## 6.14.0 - 2025-02-13

### What's Changed

* LDAP model lookup from Auth Provider by @crossplatformconsulting in https://github.com/spatie/laravel-permission/pull/2750

### Internals

* Add PHPUnit annotations, for future compatibility with PHPUnit 12 by @drbyte in https://github.com/spatie/laravel-permission/pull/2806

### New Contributors

* @crossplatformconsulting made their first contribution in https://github.com/spatie/laravel-permission/pull/2750

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/6.13.0...6.14.0

## 6.13.0 - 2025-02-05

### What's Changed

* LazyLoading: Explicitly call `loadMissing('permissions')` when the relation is needed, and test with `Model::preventLazyLoading()` by @erikn69 in https://github.com/spatie/laravel-permission/pull/2776
* [Docs] Add instructions to reinitialize cache for multi-tenancy key settings when updating multiple tenants in a single request cycle, by @sudkumar in https://github.com/spatie/laravel-permission/pull/2804

### New Contributors

* @sudkumar made their first contribution in https://github.com/spatie/laravel-permission/pull/2804

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/6.12.0...6.13.0

## 6.12.0 - 2025-01-31

### What's Changed

* Support Laravel 12

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/6.11.0...6.12.0

## 6.11.0 - 2025-01-30

### What's Changed

* Add configurable team resolver for permission team id (helpful for Jetstream, etc) by @adrenallen in https://github.com/spatie/laravel-permission/pull/2790

### Internals

* Replace php-cs-fixer with Laravel Pint by @bobbrodie in https://github.com/spatie/laravel-permission/pull/2780

### Documentation Updates

* [Docs] Include namespace in example in uuid.md by @ken-tam in https://github.com/spatie/laravel-permission/pull/2764
* [Docs] Include Laravel 11 example in exceptions.md by @frankliniwobi in https://github.com/spatie/laravel-permission/pull/2768
* [Docs] Fix typo in code example in passport.md by @m3skalina in https://github.com/spatie/laravel-permission/pull/2782
* [Docs] Correct username in new-app.md by @trippodi in https://github.com/spatie/laravel-permission/pull/2785
* [Docs] Add composer specificity by @imanghafoori1 in https://github.com/spatie/laravel-permission/pull/2772
* [Docs] Update installation-laravel.md to fix providers.php location. by @curiousteam in https://github.com/spatie/laravel-permission/pull/2796

### New Contributors

* @ken-tam made their first contribution in https://github.com/spatie/laravel-permission/pull/2764
* @frankliniwobi made their first contribution in https://github.com/spatie/laravel-permission/pull/2768
* @bobbrodie made their first contribution in https://github.com/spatie/laravel-permission/pull/2780
* @m3skalina made their first contribution in https://github.com/spatie/laravel-permission/pull/2782
* @trippodi made their first contribution in https://github.com/spatie/laravel-permission/pull/2785
* @imanghafoori1 made their first contribution in https://github.com/spatie/laravel-permission/pull/2772
* @curiousteam made their first contribution in https://github.com/spatie/laravel-permission/pull/2796
* @adrenallen made their first contribution in https://github.com/spatie/laravel-permission/pull/2790

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/6.10.1...6.11.0

## 6.10.1 - 2024-11-08

### What's Changed

* Fix #2749 regression bug in `6.10.0` : "Can no longer delete permissions" by @erikn69 in https://github.com/spatie/laravel-permission/pull/2759

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/6.10.0...6.10.1

## 6.10.0 - 2024-11-05

### What's Changed

* Fix `GuardDoesNotMatch should accept collection` by @erikn69 in https://github.com/spatie/laravel-permission/pull/2748
* Improve performance for hydrated collections by @inserve-paul in https://github.com/spatie/laravel-permission/pull/2749
* Only show error if `cache key exists` and `forgetCachedPermissions` fails by @erikn69 in https://github.com/spatie/laravel-permission/pull/2707
* Remove v5 cache fallback alias by @drbyte in https://github.com/spatie/laravel-permission/pull/2754
* Include `Larastan` in `dev` by @drbyte in https://github.com/spatie/laravel-permission/pull/2755

#### Docs

* [Docs example] Check for 'all' or 'any' permissions before specific permissions by @ceilidhboy in https://github.com/spatie/laravel-permission/pull/2694
* [Docs] Fix typo in uuid.md by @levizoesch in https://github.com/spatie/laravel-permission/pull/2705
* [Docs] Upgrade Guide - Add PR links to upgrade guide by @mraheelkhan in https://github.com/spatie/laravel-permission/pull/2716
* [Docs] use more modern syntax for nullable return type by @galangaidilakbar in https://github.com/spatie/laravel-permission/pull/2719
* [Docs] camelCase variable naming in example by @KamilWojtalak in https://github.com/spatie/laravel-permission/pull/2723
* [Docs] Update using-policies.md by @marcleonhard in https://github.com/spatie/laravel-permission/pull/2741
* [Docs] Example of pushing custom middleware before SubstituteBindings middleware by @WyattCast44 in https://github.com/spatie/laravel-permission/pull/2740

#### Other

* PHP 8.4 tests by @erikn69 in https://github.com/spatie/laravel-permission/pull/2747
* Fix comment typo by @machacekmartin in https://github.com/spatie/laravel-permission/pull/2753

### New Contributors

* @ceilidhboy made their first contribution in https://github.com/spatie/laravel-permission/pull/2694
* @levizoesch made their first contribution in https://github.com/spatie/laravel-permission/pull/2705
* @galangaidilakbar made their first contribution in https://github.com/spatie/laravel-permission/pull/2719
* @KamilWojtalak made their first contribution in https://github.com/spatie/laravel-permission/pull/2723
* @marcleonhard made their first contribution in https://github.com/spatie/laravel-permission/pull/2741
* @WyattCast44 made their first contribution in https://github.com/spatie/laravel-permission/pull/2740
* @inserve-paul made their first contribution in https://github.com/spatie/laravel-permission/pull/2749
* @machacekmartin made their first contribution in https://github.com/spatie/laravel-permission/pull/2753

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/6.9.0...6.10.0

## 6.9.0 - 2024-06-22

### What's Changed

* Use `->withPivot()` for teamed relationships (allows `getPivotColumns()`) by @juliangums in https://github.com/spatie/laravel-permission/pull/2679
* Update docblock on `$role->hasPermissionTo()` to include `BackedEnum` by @drbyte co-authored by @SanderMuller
* [Docs] Clarify that `$guard_name` can be an array by @angelej in https://github.com/spatie/laravel-permission/pull/2659
* Fix misc typos in changelog by @szepeviktor in https://github.com/spatie/laravel-permission/pull/2686

### New Contributors

* @angelej made their first contribution in https://github.com/spatie/laravel-permission/pull/2659
* @SanderMuller made their first contribution in #2676
* @szepeviktor made their first contribution in https://github.com/spatie/laravel-permission/pull/2686

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/6.8.0...6.9.0

## 6.8.0 - 2024-06-21

### What's Changed

* Fix can't save the same model twice by @erikn69 in https://github.com/spatie/laravel-permission/pull/2658
* Fix phpstan from #2616 by @erikn69 in https://github.com/spatie/laravel-permission/pull/2685

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/6.7.0...6.8.0

## 6.7.0 - 2024-04-19

### What's Changed

- Fixed remaining Octane event contract. Update to #2656 in release `6.5.0`

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/6.6.0...6.7.0

## 6.6.0 - 2024-04-19

### What's Changed

* Roles: Support for casting role names to enums by @gajosadrian in https://github.com/spatie/laravel-permission/pull/2616
* Fix permission:show UUID error #2581 by @drbyte in https://github.com/spatie/laravel-permission/pull/2582
* Cover WildcardPermission instance verification based on its own guard (Allow hasAllPermissions and hasAnyPermission to run on custom guard for WildcardPermission) by @AlexandreBellas in https://github.com/spatie/laravel-permission/pull/2608
* Register Laravel "About" details by @drbyte in https://github.com/spatie/laravel-permission/pull/2584

### New Contributors

* @gajosadrian made their first contribution in https://github.com/spatie/laravel-permission/pull/2616
* @AlexandreBellas made their first contribution in https://github.com/spatie/laravel-permission/pull/2608

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/6.5.0...6.6.0

## 6.5.0 - 2024-04-18

### What's Changed

* Octane: Fix wrong event listener by @erikn69 in https://github.com/spatie/laravel-permission/pull/2656
* Teams: Add nullable team_id by @Androlax2 in https://github.com/spatie/laravel-permission/pull/2607
* Blade: simplify the definition of multiple Blade "if" directives by @alissn in https://github.com/spatie/laravel-permission/pull/2628
* DocBlocks: Update HasPermissions::collectPermissions() docblock by @Plytas in https://github.com/spatie/laravel-permission/pull/2641

#### Internals

* Update role-permissions.md by @killjin in https://github.com/spatie/laravel-permission/pull/2631
* Bump ramsey/composer-install from 2 to 3 by @dependabot in https://github.com/spatie/laravel-permission/pull/2630
* Bump dependabot/fetch-metadata from 1 to 2 by @dependabot in https://github.com/spatie/laravel-permission/pull/2642

### New Contributors

* @alissn made their first contribution in https://github.com/spatie/laravel-permission/pull/2628
* @Androlax2 made their first contribution in https://github.com/spatie/laravel-permission/pull/2607
* @Plytas made their first contribution in https://github.com/spatie/laravel-permission/pull/2641
* @killjin made their first contribution in https://github.com/spatie/laravel-permission/pull/2631

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/6.4.0...6.5.0

## 6.4.0 - 2024-02-28

* Laravel 11 Support

### What's Changed

* Add Laravel 11 to workflow run tests by @mraheelkhan in https://github.com/spatie/laravel-permission/pull/2605
* And Passport 12

### Internals

* Update to use Larastan Org by @arnebr in https://github.com/spatie/laravel-permission/pull/2585
* laravel-pint-action to major version tag by @erikn69 in https://github.com/spatie/laravel-permission/pull/2586

### New Contributors

* @arnebr made their first contribution in https://github.com/spatie/laravel-permission/pull/2585
* @mraheelkhan made their first contribution in https://github.com/spatie/laravel-permission/pull/2605

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/6.3.0...6.4.0

## 6.3.0 - 2023-12-24

### What's Changed

* Octane Fix: Clear wildcard permissions on Tick in https://github.com/spatie/laravel-permission/pull/2583

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/6.2.0...6.3.0

## 6.2.0 - 2023-12-09

### What's Changed

* Skip duplicates on sync (was triggering Integrity Constraint Violation error) by @erikn69 in https://github.com/spatie/laravel-permission/pull/2574

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/6.1.0...6.2.0

## 6.1.0 - 2023-11-09

### What's Changed

- Reset teamId on Octane by @erikn69 in https://github.com/spatie/laravel-permission/pull/2547
  NOTE: The `\Spatie\Permission\Listeners\OctaneReloadPermissions` listener introduced in 6.0.0 is removed in 6.1.0, because the logic is directly incorporated into the ServiceProvider now.
  
  Thanks @jameshulse for the heads-up and code-review.
  

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/6.0.1...6.1.0

## 6.0.1 - 2023-11-06

### What's Changed

- Provide a default team_foreign_key value in case config file isn't upgraded yet or teams feature is unused. Fixes #2535
- [Docs] Update unsetRelation() example in teams-permissions.md by @shdehnavi in https://github.com/spatie/laravel-permission/pull/2534
- [Docs] Update link in direct-permissions.md by @sevannerse in https://github.com/spatie/laravel-permission/pull/2539

### New Contributors

- @sevannerse made their first contribution in https://github.com/spatie/laravel-permission/pull/2539

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/6.0.0...6.0.1

## 6.0.0 - 2023-10-25

### What's Changed

- Full uuid/guid/ulid support by @erikn69 in https://github.com/spatie/laravel-permission/pull/2089
- Refactor: Change static properties to non-static by @olivernybroe in https://github.com/spatie/laravel-permission/pull/2324
- Fix Role::withCount if belongsToMany declared by @xenaio-daniil in https://github.com/spatie/laravel-permission/pull/2280
- Fix: Lazily bind dependencies by @olivernybroe in https://github.com/spatie/laravel-permission/pull/2321
- Avoid loss of all permissions/roles pivots on sync error by @erikn69 in https://github.com/spatie/laravel-permission/pull/2341
- Fix delete permissions on Permissions Model by @erikn69 in https://github.com/spatie/laravel-permission/pull/2366
- Detach users on role/permission physical deletion by @erikn69 in https://github.com/spatie/laravel-permission/pull/2370
- Rename clearClassPermissions method to clearPermissionsCollection by @erikn69 in https://github.com/spatie/laravel-permission/pull/2369
- Use anonymous migrations (for L8+) by @erikn69 in https://github.com/spatie/laravel-permission/pull/2374
- [BC] Return string on getPermissionClass(), getRoleClass() by @erikn69 in https://github.com/spatie/laravel-permission/pull/2368
- Only offer publishing when running in console by @erikn69 in https://github.com/spatie/laravel-permission/pull/2377
- Don't add commands in web interface context by @angeljqv in https://github.com/spatie/laravel-permission/pull/2405
- [BC] Fix Role->hasPermissionTo() signature to match HasPermissions trait by @erikn69 in https://github.com/spatie/laravel-permission/pull/2380
- Force that getPermissionsViaRoles, hasPermissionViaRole must be used only by authenticable by @erikn69 in https://github.com/spatie/laravel-permission/pull/2382
- fix BadMethodCallException: undefined methods hasAnyRole, hasAnyPermissions by @erikn69 in https://github.com/spatie/laravel-permission/pull/2381
- Add PHPStan workflow with fixes by @erikn69 in https://github.com/spatie/laravel-permission/pull/2376
- Add BackedEnum support by @drbyte in https://github.com/spatie/laravel-permission/pull/2391
- Drop PHP 7.3 support by @angeljqv in https://github.com/spatie/laravel-permission/pull/2388
- Drop PHP 7.4 support by @drbyte in https://github.com/spatie/laravel-permission/pull/2485
- Test against PHP 8.3 by @erikn69 in https://github.com/spatie/laravel-permission/pull/2512
- Fix call to an undefined method Role::getRoleClass by @erikn69 in https://github.com/spatie/laravel-permission/pull/2411
- Remove force loading model relationships by @erikn69 in https://github.com/spatie/laravel-permission/pull/2412
- Test alternate cache drivers by @erikn69 in https://github.com/spatie/laravel-permission/pull/2416
- Use attach instead of sync on traits by @erikn69 in https://github.com/spatie/laravel-permission/pull/2420
- Fewer sqls in syncRoles, syncPermissions by @erikn69 in https://github.com/spatie/laravel-permission/pull/2423
- Add middleware using static method by @jnoordsij in https://github.com/spatie/laravel-permission/pull/2424
- Update PHPDocs for IDE autocompletion by @erikn69 in https://github.com/spatie/laravel-permission/pull/2437
- [BC] Wildcard permissions algorithm performance improvements (ALERT: Breaking Changes) by @danharrin in https://github.com/spatie/laravel-permission/pull/2445
- Add withoutRole and withoutPermission scopes by @drbyte in https://github.com/spatie/laravel-permission/pull/2463
- Add support for service-to-service Passport client by @SuperDJ in https://github.com/spatie/laravel-permission/pull/2467
- Register OctaneReloadPermissions listener for Laravel Octane by @erikn69 in https://github.com/spatie/laravel-permission/pull/2403
- Add guard name to exceptions by @drbyte in https://github.com/spatie/laravel-permission/pull/2481
- Update contracts to allow for UUID by @drbyte in https://github.com/spatie/laravel-permission/pull/2480
- Avoid triggering eloquent.retrieved event by @erikn69 in https://github.com/spatie/laravel-permission/pull/2498
- [BC] Rename "Middlewares" namespace to "Middleware" by @drbyte in https://github.com/spatie/laravel-permission/pull/2499
- `@haspermission` directive by @axlwild in https://github.com/spatie/laravel-permission/pull/2515
- Add guard parameter to can() by @drbyte in https://github.com/spatie/laravel-permission/pull/2526

### New Contributors

- @xenaio-daniil made their first contribution in https://github.com/spatie/laravel-permission/pull/2280
- @JensvandeWiel made their first contribution in https://github.com/spatie/laravel-permission/pull/2336
- @fsamapoor made their first contribution in https://github.com/spatie/laravel-permission/pull/2361
- @yungifez made their first contribution in https://github.com/spatie/laravel-permission/pull/2394
- @HasanEksi made their first contribution in https://github.com/spatie/laravel-permission/pull/2418
- @jnoordsij made their first contribution in https://github.com/spatie/laravel-permission/pull/2424
- @danharrin made their first contribution in https://github.com/spatie/laravel-permission/pull/2445
- @SuperDJ made their first contribution in https://github.com/spatie/laravel-permission/pull/2467
- @ChillMouse made their first contribution in https://github.com/spatie/laravel-permission/pull/2438
- @Okipa made their first contribution in https://github.com/spatie/laravel-permission/pull/2492
- @edalzell made their first contribution in https://github.com/spatie/laravel-permission/pull/2494
- @sirosfakhri made their first contribution in https://github.com/spatie/laravel-permission/pull/2501
- @juliangums made their first contribution in https://github.com/spatie/laravel-permission/pull/2516
- @nnnnnnnngu made their first contribution in https://github.com/spatie/laravel-permission/pull/2524
- @axlwild made their first contribution in https://github.com/spatie/laravel-permission/pull/2515
- @shdehnavi made their first contribution in https://github.com/spatie/laravel-permission/pull/2527

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.11.1...6.0.0

## 5.11.1 - 2023-10-25

No functional changes. Just several small updates to the Documentation.

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.11.0...5.11.1

## 5.11.0 - 2023-08-30

### What's Changed

- [V5] Avoid triggering `eloquent.retrieved` event by @erikn69 in https://github.com/spatie/laravel-permission/pull/2490

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.10.2...5.11.0

## 5.10.2 - 2023-07-04

### What's Changed

- Fix Eloquent Strictness on `permission:show` Command by @erikn69 in https://github.com/spatie/laravel-permission/pull/2457

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.10.1...5.10.2

## 5.10.1 - 2023-04-12

### What's Changed

- [V5] Fix artisan command `permission:show` output of roles with underscores by @erikn69 in https://github.com/spatie/laravel-permission/pull/2396

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.10.0...5.10.1

## 5.10.0 - 2023-03-22

### What's Changed

- Fix delete permissions on Permissions Model by @erikn69 in https://github.com/spatie/laravel-permission/pull/2366

## 5.9.1 - 2023-02-06

Apologies for the break caused by 5.9.0 !

### Reverted Lazy binding of dependencies.

- Revert "fix: Lazily bind dependencies", originally #2309

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.9.0...5.9.1

## 5.9.0 - 2023-02-06

### What's Changed

- Add `permission-` prefix to publish tag names by @sedehi in https://github.com/spatie/laravel-permission/pull/2301
- Fix detaching user models on teams feature #2220 by @erikn69 in https://github.com/spatie/laravel-permission/pull/2221
- Hint model properties by @AJenbo in https://github.com/spatie/laravel-permission/pull/2230
- Custom wildcard verification/separators support by @erikn69 in https://github.com/spatie/laravel-permission/pull/2252
- fix: Lazily bind dependencies by @olivernybroe in https://github.com/spatie/laravel-permission/pull/2309
- Extract query to `getPermissionsWithRoles` method. by @xiCO2k in https://github.com/spatie/laravel-permission/pull/2316
- This will allow to extend the PermissionRegistrar class and change the query.

### New Contributors

- @sedehi made their first contribution in https://github.com/spatie/laravel-permission/pull/2301
- @parallels999 made their first contribution in https://github.com/spatie/laravel-permission/pull/2265
- @AJenbo made their first contribution in https://github.com/spatie/laravel-permission/pull/2230
- @olivernybroe made their first contribution in https://github.com/spatie/laravel-permission/pull/2309
- @xiCO2k made their first contribution in https://github.com/spatie/laravel-permission/pull/2316

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.8.0...5.9.0

## 5.8.0 - 2023-01-14

### What's Changed

- Laravel 10.x Support by @erikn69 in https://github.com/spatie/laravel-permission/pull/2298

#### Administrative

- [Docs] Link updated to match name change of related tool repo by @aliqasemzadeh in https://github.com/spatie/laravel-permission/pull/2253
- Fix tests badge by @erikn69 in https://github.com/spatie/laravel-permission/pull/2300
- Add Laravel Pint Support by @patinthehat in https://github.com/spatie/laravel-permission/pull/2269
- Normalize composer.json by @patinthehat in https://github.com/spatie/laravel-permission/pull/2259
- Add Dependabot Automation by @patinthehat in https://github.com/spatie/laravel-permission/pull/2257

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.7.0...5.8.0

## 5.7.0 - 2022-11-23

### What's Changed

- [Bugfix] Avoid checking permissions-via-roles on `Role` model (ref `Model::preventAccessingMissingAttributes()`) by @juliomotol in https://github.com/spatie/laravel-permission/pull/2227

### New Contributors

- @juliomotol made their first contribution in https://github.com/spatie/laravel-permission/pull/2227

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.6.0...5.7.0

## 5.6.0 - 2022-11-19

### What's Changed

- No longer throws an exception when checking `hasAllPermissions()` if the permission name does not exist by @mtawil in https://github.com/spatie/laravel-permission/pull/2248

### Doc Updates

- [Docs] Add syncPermissions() in role-permissions.md by @xorinzor in https://github.com/spatie/laravel-permission/pull/2235
- [Docs] Fix broken Link that link to freek's blog post by @chengkangzai in https://github.com/spatie/laravel-permission/pull/2234

### New Contributors

- @xorinzor made their first contribution in https://github.com/spatie/laravel-permission/pull/2235
- @chengkangzai made their first contribution in https://github.com/spatie/laravel-permission/pull/2234
- @mtawil made their first contribution in https://github.com/spatie/laravel-permission/pull/2248

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.5.16...5.6.0

## 5.5.16 - 2022-10-23

### What's Changed

- optimize `for` loop in WildcardPermission by @SubhanSh in https://github.com/spatie/laravel-permission/pull/2113

### New Contributors

- @SubhanSh made their first contribution in https://github.com/spatie/laravel-permission/pull/2113

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.5.15...5.5.16

## 5.5.15 - 2022-10-23

Autocomplete all Blade directives via Laravel Idea plugin

### What's Changed

- Autocomplete all Blade directives via Laravel Idea plugin by @maartenpaauw in https://github.com/spatie/laravel-permission/pull/2210
- Add tests for display roles/permissions on UnauthorizedException by @erikn69 in https://github.com/spatie/laravel-permission/pull/2228

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.5.14...5.5.15

## 5.5.14 - 2022-10-21

FIXED BREAKING CHANGE. (Sorry about that!)

### What's Changed

- Revert "Avoid calling the config helper in the role/perm model constructor" by @drbyte in https://github.com/spatie/laravel-permission/pull/2225

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.5.13...5.5.14

## 5.5.13 - 2022-10-21

### What's Changed

- fix UnauthorizedException: Wrong configuration was used in forRoles by @Sy-Dante in https://github.com/spatie/laravel-permission/pull/2224

### New Contributors

- @Sy-Dante made their first contribution in https://github.com/spatie/laravel-permission/pull/2224

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.5.12...5.5.13

## 5.5.12 - 2022-10-19

Fix regression introduced in `5.5.10`

### What's Changed

- Fix undefined index guard_name by @erikn69 in https://github.com/spatie/laravel-permission/pull/2219

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.5.11...5.5.12

## 5.5.11 - 2022-10-19

### What's Changed

- Support static arrays on blade directives by @erikn69 in https://github.com/spatie/laravel-permission/pull/2168

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.5.10...5.5.11

## 5.5.10 - 2022-10-19

### What's Changed

- Avoid calling the config helper in the role/perm model constructor by @adiafora in https://github.com/spatie/laravel-permission/pull/2098 as discussed in https://github.com/spatie/laravel-permission/issues/2097 regarding `DI`

### New Contributors

- @adiafora made their first contribution in https://github.com/spatie/laravel-permission/pull/2098

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.5.9...5.5.10

## 5.5.9 - 2022-10-19

Compatibility Bugfix

### What's Changed

- Prevent `MissingAttributeException` for `guard_name` by @ejunker in https://github.com/spatie/laravel-permission/pull/2216

### New Contributors

- @ejunker made their first contribution in https://github.com/spatie/laravel-permission/pull/2216

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.5.8...5.5.9

## 5.5.8 - 2022-10-19

`HasRoles` trait

### What's Changed

- Fix returning all roles instead of the assigned by @erikn69 in https://github.com/spatie/laravel-permission/pull/2194

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.5.7...5.5.8

## 5.5.7 - 2022-10-19

Optimize HasPermissions trait

### What's Changed

- Delegate permission collection filter to another method by @angeljqv in https://github.com/spatie/laravel-permission/pull/2182
- Delegate permission filter to another method by @angeljqv in https://github.com/spatie/laravel-permission/pull/2183

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.5.6...5.5.7

## 5.5.6 - 2022-10-19

Just a maintenance release.

### What's Changed

- Actions: add PHP 8.2 Build by @erikn69 in https://github.com/spatie/laravel-permission/pull/2214
- Docs: Fix small syntax error in teams-permissions.md by @miten5 in https://github.com/spatie/laravel-permission/pull/2171
- Docs: Update documentation for multiple guards by @gms8994 in https://github.com/spatie/laravel-permission/pull/2169
- Docs: Make Writing Policies link clickable by @maartenpaauw in https://github.com/spatie/laravel-permission/pull/2202
- Docs: Add note about non-standard User models by @androidacy-user in https://github.com/spatie/laravel-permission/pull/2179
- Docs: Fix explanation of results for hasAllDirectPermissions in role-permission.md by @drdan18 in https://github.com/spatie/laravel-permission/pull/2139
- Docs: Add ULIDs reference by @erikn69 in https://github.com/spatie/laravel-permission/pull/2213

### New Contributors

- @miten5 made their first contribution in https://github.com/spatie/laravel-permission/pull/2171
- @gms8994 made their first contribution in https://github.com/spatie/laravel-permission/pull/2169
- @maartenpaauw made their first contribution in https://github.com/spatie/laravel-permission/pull/2202
- @androidacy-user made their first contribution in https://github.com/spatie/laravel-permission/pull/2179
- @drdan18 made their first contribution in https://github.com/spatie/laravel-permission/pull/2139

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.5.5...5.5.6

## 5.5.5 - 2022-06-29

### What's Changed

- Custom primary keys tests(Only tests) by @erikn69 in https://github.com/spatie/laravel-permission/pull/2096
- [PHP 8.2] Fix `${var}` string interpolation deprecation by @Ayesh in https://github.com/spatie/laravel-permission/pull/2117
- Use `getKey`, `getKeyName` instead of `id` by @erikn69 in https://github.com/spatie/laravel-permission/pull/2116
- On WildcardPermission class use static instead of self for extending by @erikn69 in https://github.com/spatie/laravel-permission/pull/2111
- Clear roles array after hydrate from cache by @angeljqv in https://github.com/spatie/laravel-permission/pull/2099

### New Contributors

- @Ayesh made their first contribution in https://github.com/spatie/laravel-permission/pull/2117

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.5.4...5.5.5

## 5.5.4 - 2022-05-16

## What's Changed

- Support custom primary key names on models by @erikn69 in https://github.com/spatie/laravel-permission/pull/2092
- Fix UuidTrait on uuid doc page by @abhishekpaul in https://github.com/spatie/laravel-permission/pull/2094
- Support custom fields on cache by @erikn69 in https://github.com/spatie/laravel-permission/pull/2091

## New Contributors

- @abhishekpaul made their first contribution in https://github.com/spatie/laravel-permission/pull/2094

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.5.3...5.5.4

## 5.5.3 - 2022-05-05

## What's Changed

- Update .gitattributes by @angeljqv in https://github.com/spatie/laravel-permission/pull/2065
- Remove double semicolon from add_teams_fields.php.stub by @morganarnel in https://github.com/spatie/laravel-permission/pull/2067
- [V5] Allow revokePermissionTo to accept Permission[] by @erikn69 in https://github.com/spatie/laravel-permission/pull/2014
- [V5] Improve typing in role's findById and findOrCreate method by @itsfaqih in https://github.com/spatie/laravel-permission/pull/2022
- [V5] Cache loader improvements by @erikn69 in https://github.com/spatie/laravel-permission/pull/1912

## New Contributors

- @morganarnel made their first contribution in https://github.com/spatie/laravel-permission/pull/2067
- @itsfaqih made their first contribution in https://github.com/spatie/laravel-permission/pull/2022

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.5.2...5.5.3

## 5.5.2 - 2022-03-09

## What's Changed

- [Fixes BIG bug] register blade directives after resolving blade compiler by @tabacitu in https://github.com/spatie/laravel-permission/pull/2048

## New Contributors

- @tabacitu made their first contribution in https://github.com/spatie/laravel-permission/pull/2048

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.5.1...5.5.2

## 5.5.1 - 2022-03-03

## What's Changed

- Spelling correction by @gergo85 in https://github.com/spatie/laravel-permission/pull/2024
- update broken link to laravel exception by @kingzamzon in https://github.com/spatie/laravel-permission/pull/2023
- Fix Blade Directives incompatibility with renderers by @erikn69 in https://github.com/spatie/laravel-permission/pull/2039

## New Contributors

- @gergo85 made their first contribution in https://github.com/spatie/laravel-permission/pull/2024
- @kingzamzon made their first contribution in https://github.com/spatie/laravel-permission/pull/2023

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.5.0...5.5.1

## 5.5.0 - 2022-01-11

- add support for Laravel 9

## 5.4.0 - 2021-11-17

## What's Changed

- Add support for PHP 8.1 by @freekmurze in https://github.com/spatie/laravel-permission/pull/1926

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.3.2...5.4.0

## 5.3.2 - 2021-11-17

## What's Changed

- [V5] Support for custom key names on Role,Permission by @erikn69 in https://github.com/spatie/laravel-permission/pull/1913

**Full Changelog**: https://github.com/spatie/laravel-permission/compare/5.3.1...5.3.2

## 5.3.1 - 2021-11-04

- Fix hints, support int on scopePermission (#1908)

## 5.3.0 - 2021-10-29

- Option for custom logic for checking permissions (#1891)

## 5.2.0 - 2021-10-28

- [V5] Fix detaching on all teams instead of only current #1888 by @erikn69 in https://github.com/spatie/laravel-permission/pull/1890
- [V5] Add uuid compatibility support on teams by @erikn69 in https://github.com/spatie/laravel-permission/pull/1857
- Adds setRoleClass method to PermissionRegistrar by @timschwartz in https://github.com/spatie/laravel-permission/pull/1867
- Load permissions for preventLazyLoading by @bahramsadin in https://github.com/spatie/laravel-permission/pull/1884
- [V5] Doc for `Super Admin` on teams by @erikn69 in https://github.com/spatie/laravel-permission/pull/1845

## 5.1.1 - 2021-09-01

- Avoid Roles over-hydration #1834

## 5.1.0 - 2021-08-31

- No longer flush cache on User role/perm assignment changes #1832
- NOTE:  You should test your app to be sure that you don't accidentally have deep dependencies on cache resets happening automatically in these cases.
- ALSO NOTE: If you have added custom code which depended on these flush operations, you may need to add your own cache-reset calls.

## 5.0.0 - 2021-08-31

- Change default-guard-lookup to prefer current user's guard (see BC note in #1817 )
- Teams/Groups feature (see docs, or PR #1804)
- Customized pivots instead of `role_id`,`permission_id` #1823

## 4.4.1 - 2021-09-01

- Avoid Roles over-hydration #1834

## 4.4.0 - 2021-08-28

- Avoid BC break (removed interface change) on cache change added in 4.3.0 #1826
- Made cache even smaller #1826
- Avoid re-sync on non-persisted objects when firing Eloquent::saved #1819

## 4.3.0 - 2021-08-17

- Speed up permissions cache lookups, and make cache smaller #1799

## 4.2.0 - 2021-06-04

- Add hasExactRoles method #1696

## 4.1.0 - 2021-06-01

- Refactor to resolve guard only once during middleware
- Refactor service provider by extracting some methods

## 4.0.1 - 2021-03-22

- Added note in migration for field lengths on MySQL 8. (either shorten the columns to 125 or use InnoDB)

## 4.0.0 - 2021-01-27

- Drop support on Laravel 5.8 #1615
- Fix bug when adding roles to a model that doesn't yet exist #1663
- Enforce unique constraints on database level #1261
- Changed PermissionRegistrar::initializeCache() public to allow reinitializing cache in custom situations. #1521
- Use Eloquent\Collection instead of Support\Collection for consistency, collection merging, etc #1630

This package now requires PHP 7.2.5 and Laravel 6.0 or higher.
If you are on a PHP version below 7.2.5 or a Laravel version below 6.0 you can use an older version of this package.

## 3.18.0 - 2020-11-27

- Allow PHP 8.0

## 3.17.0 - 2020-09-16

- Optional `$guard` parameter may be passed to `RoleMiddleware`, `PermissionMiddleware`, and `RoleOrPermissionMiddleware`. See #1565

## 3.16.0 - 2020-08-18

- Added Laravel 8 support

## 3.15.0 - 2020-08-15

- Change `users` relationship type to BelongsToMany

## 3.14.0 - 2020-08-15

- Declare table relations earlier to improve guarded/fillable detection accuracy (relates to Aug 2020 Laravel security patch)

## 3.13.0 - 2020-05-19

- Provide migration error text to stop caching local config when installing packages.

## 3.12.0 - 2020-05-14

- Add missing config setting for `display_role_in_exception`
- Ensure artisan `permission:show` command uses configured models

## 3.11.0 - 2020-03-03

- Allow guardName() as a function with priority over $guard_name property #1395

## 3.10.1 - 2020-03-03

- Update patch to handle intermittent error in #1370

## 3.10.0 - 2020-03-02

- Ugly patch to handle intermittent error: `Trying to access array offset on value of type null` in #1370

## 3.9.0 - 2020-02-26

- Add Wildcard Permissions feature #1381 (see PR or docs for details)

## 3.8.0 - 2020-02-18

- Clear in-memory permissions on boot, for benefit of long running processes like Swoole. #1378

## 3.7.2 - 2020-02-17

- Refine test for Lumen dependency. Ref #1371, Fixes #1372.

## 3.7.1 - 2020-02-15

- Internal refactoring of scopes to use whereIn instead of orWhere #1334, #1335
- Internal refactoring to flatten collection on splat #1341

## 3.7.0 - 2020-02-15

- Added methods to check any/all when querying direct permissions #1245
- Removed older Lumen dependencies #1371

## 3.6.0 - 2020-01-17

- Added Laravel 7.0 support
- Allow splat operator for passing roles to `hasAnyRole()`

## 3.5.0 - 2020-01-07

- Added missing `guardName` to Exception `PermissionDoesNotExist` #1316

## 3.4.1 - 2019-12-28

- Fix 3.4.0 for Lumen

## 3.4.0 - 2019-12-27

- Make compatible with Swoole - ie: for long-running Laravel instances

## 3.3.1 - 2019-12-24

- Expose Artisan commands to app layer, not just to console

## 3.3.0 - 2019-11-22

- Remove duplicate and unreachable code
- Remove checks for older Laravel versions

## 3.2.0 - 2019-10-16

- Implementation of optional guard check for hasRoles and hasAllRoles - See #1236

## 3.1.0 - 2019-10-16

- Use bigIncrements/bigInteger in migration - See #1224

## 3.0.0 - 2019-09-02

- Update dependencies to allow for Laravel 6.0
- Drop support for Laravel 5.7 and older, and PHP 7.1 and older. (They can use v2 of this package until they upgrade.)
- To be clear: v3 requires minimum Laravel 5.8 and PHP 7.2

## 2.38.0 - 2019-09-02

- Allow support for multiple role/permission models
- Load roles relationship only when missing
- Wrap helpers in function_exists() check

## 2.37.0 - 2019-04-09

- Added `permission:show` CLI command to display a table of roles/permissions
- `removeRole` now returns the model, consistent with other methods
- model `$guarded` properties updated to `protected`
- README updates

## 2.36.1 - 2019-03-05

- reverts the changes made in 2.36.0 due to some reported breaks.

## 2.36.0 - 2019-03-04

- improve performance by reducing another iteration in processing query results and returning earlier

## 2.35.0 - 2019-03-01

- overhaul internal caching strategy for better performance and fix cache miss when permission names contained spaces
- deprecated hasUncachedPermissionTo() (use hasPermissionTo() instead)
- added getPermissionNames() method

## 2.34.0 - 2019-02-26

- Add explicit pivotKeys to roles/permissions BelongsToMany relationships

## 2.33.0 - 2019-02-20

- Laravel 5.8 compatibility

## 2.32.0 - 2019-02-13

- Fix duplicate permissions being created through artisan command

## 2.31.0 - 2019-02-03

- Add custom guard query to role scope
- Remove use of array_wrap helper function due to future deprecation

## 2.30.0 - 2019-01-28

- Change cache config time to DateInterval instead of integer

This is in preparation for compatibility with Laravel 5.8's cache TTL change to seconds instead of minutes.

NOTE: If you leave your existing `config/permission.php` file alone, then with Laravel 5.8 the `60 * 24` will change from being treated as 24 hours to just 24 minutes. Depending on your app, this may or may not make a significant difference.  Updating your config file to a specific DateInterval will add specificity and insulate you from the TTL change in Laravel 5.8.

Refs:

https://laravel-news.com/cache-ttl-change-coming-to-laravel-5-8
https://github.com/laravel/framework/commit/fd6eb89b62ec09df1ffbee164831a827e83fa61d

## 2.29.0 - 2018-12-15

- Fix bound `saved` event from firing on all subsequent models when calling assignRole or givePermissionTo on unsaved models. However, it is preferable to save the model first, and then add roles/permissions after saving. See #971.

## 2.28.2 - 2018-12-10

- Use config settings for cache reset in migration stub

## 2.28.1 - 2018-12-07

- Remove use of Cache facade, for Lumen compatibility

## 2.28.0 - 2018-11-30

- Rename `getCacheKey` method in HasPermissions trait to `getPermissionCacheKey` for clearer specificity.

## 2.27.0 - 2018-11-21

- Add ability to specify a cache driver for roles/permissions caching

## 2.26.2 - 2018-11-20

- Added the ability to reset the permissions cache via an Artisan command:
- `php artisan permission:cache-reset`

## 2.26.1 - 2018-11-19

- minor update to de-duplicate code overhead
- numerous internal updates to cache tests infrastructure

## 2.26.0 - 2018-11-19

- Substantial speed increase by caching the associations between models and permissions

### NOTES:

The following changes are not "breaking", but worth making the updates to your app for consistency.

1. Config file: The `config/permission.php` file changed to move cache-related settings into a sub-array. **You should review the changes and merge the updates into your own config file.** Specifically the `expiration_time` value has moved into a sub-array entry, and the old top-level entry is no longer used.
2. See the original config file here:
3. https://github.com/spatie/laravel-permission/blob/main/config/permission.php
4. 
5. Cache Resets: If your `app` or `tests` are clearing the cache by specifying the cache key, **it is better to use the built-in forgetCachedPermissions() method** so that it properly handles tagged cache entries. Here is the recommended change:
6. 

```diff
- app()['cache']->forget('spatie.permission.cache');
+ $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();





























```
1. Also this is a good time to point out that now with v2.25.0 and v2.26.0 most permission-cache-reset scenarios may no longer be needed in your app, so it's worth reviewing those cases, as you may gain some app speed improvement by removing unnecessary cache resets.

## 2.25.0 - 2018-11-07

- A model's `roles` and `permissions` relations (respectively) are now automatically reloaded after an Assign/Remove role or Grant/Revoke of permissions. This means there's no longer a need to call `-&amp;gt;fresh()` on the model if the only reason is to reload the role/permission relations. (That said, you may want to call it for other reasons.)
- Added support for passing id to HasRole()

## 2.24.0 - 2018-11-06

- Fix operator used on RoleOrPermissionMiddleware, and avoid throwing PermissionDoesNotExist if invalid permission passed
- Auto-reload model role relation after using AssignRole
- Avoid empty permission creation when using the CreateRole command

## 2.23.0 - 2018-10-15

- Avoid unnecessary queries of user roles when fetching all permissions

## 2.22.1 - 2018-10-15

- Fix Lumen issue with Route helper added in 2.22.0

## 2.22.0 - 2018-10-11

- Added `Route::role()` and `Route::permission()` middleware helper functions
- Added new `role_or_permission` middleware to allow specifying "or" combinations

## 2.21.0 - 2018-09-29

- Revert changes from 2.17.1 in order to support Lumen 5.7

## 2.20.0 - 2018-09-19

- It will sync roles/permissions to models that are not persisted, by registering a `saved` callback.
- (It would previously throw an Integrity constraint violation QueryException on the pivot table insertion.)

## 2.19.2 - 2018-09-19

- add `@elserole` directive:
- Usage:

```php
@role('roleA')
 // user hasRole 'roleA'
@elserole('roleB')
 // user hasRole 'roleB' but not 'roleA'
@endrole





























```
## 2.19.1 - 2018-09-14

- Spark-related fix to accommodate missing guard[providers] config

## 2.19.0 - 2018-09-10

- Add ability to pass in IDs or mixed values to `role` scope
- Add `@unlessrole`/`@endunlessrole` Blade directives

## 2.18.0 - 2018-09-06

- Expanded CLI `permission:create-role` command to create optionally create-and-link permissions in one command. Also now no longer throws an error if the role already exists.

## 2.17.1 - 2018-08-28

- Require laravel/framework instead of illuminate/* starting from ~5.4.0
- Removed old dependency for illuminate/database@~5.3.0 (Laravel 5.3 is not supported)

## 2.17.0 - 2018-08-24

- Laravel 5.7 compatibility

## 2.16.0 - 2018-08-20

- Replace static Permission::class and Role::class with dynamic value (allows custom models more easily)
- Added type checking in hasPermissionTo and hasDirectPermission

## 2.15.0 - 2018-08-15

- Make assigning the same role or permission twice not throw an exception

## 2.14.0 - 2018-08-13

- Allow using another key name than `model_id` by defining new `columns` array with `model_morph_key` key in config file. This improves UUID compatibility as discussed in #777.

## 2.13.0 - 2018-08-02

- Fix issue with null values passed to syncPermissions & syncRoles

## 2.12.2 - 2018-06-13

- added hasAllPermissions method

## 2.12.1 - 2018-04-23

- Reverted 2.12.0. REVERTS: "Add ability to pass guard name to gate methods like can()". Requires reworking of guard handling if we're going to add this feature.

## 2.12.0 - 2018-04-22

- Add ability to pass guard name to gate methods like can()

## 2.11.0 - 2018-04-16

- Improve speed of permission lookups with findByName, findById, findOrCreate

## 2.10.0 - 2018-04-15

- changes the type-hinted Authenticatable to Authorizable in the PermissionRegistrar.
- (Previously it was expecting models to implement the Authenticatable contract; but really that should have been Authorizable, since that's where the Gate functionality really is.)

## 2.9.2 - 2018-03-12

- Now findOrCreate() exists for both Roles and Permissions
- Internal code refactoring for future dev work

## 2.9.1 - 2018-02-23

- Permissions now support passing integer id for sync, find, hasPermissionTo and hasDirectPermissionTo

## 2.9.0 - 2018-02-07

- add compatibility with Laravel 5.6
- Allow assign/sync/remove Roles from Permission model

## 2.8.2 - 2018-02-07

- Allow a collection containing a model to be passed to role/permission scopes

## 2.8.1 - 2018-02-03

- Fix compatibility with Spark v2.0 to v5.0

## 2.8.0 - 2018-01-25

- Support getting guard_name from extended model when using static methods

## 2.7.9 - 2018-01-23

Changes related to throwing UnauthorizedException:

- When UnauthorizedException is thrown, a property is added with the expected role/permission which triggered it
- A configuration option may be set to include the list of required roles/permissions in the message

## 2.7.8 - 2018-01-02

- REVERTED: Dynamic permission_id and role_id columns according to tables name
- NOTE: This Dynamic field naming was a breaking change, so we've removed it for now.

BEST NOT TO USE v2.7.7 if you've changed tablenames in the config file.

## 2.7.7 - 2017-12-31

- updated `HasPermissions::getStoredPermission` to allow a collection to be returned, and to fix query when passing multiple permissions
- Give and revoke multiple permissions
- Dynamic permission_id and role_id columns according to tables name
- Add findOrCreate function to Permission model
- Improved Lumen support
- Allow guard name to be null for find role by id

## 2.7.6 - 2017-11-27

- added Lumen support
- updated `HasRole::assignRole` and `HasRole::syncRoles` to accept role id's in addition to role names as arguments

## 2.7.5 - 2017-10-26

- fixed `Gate::before` for custom gate callbacks

## 2.7.4 - 2017-10-26

- added cache clearing command in `up` migration for permission tables
- use config_path helper for better Lumen support

## 2.7.3 - 2017-10-21

- refactor middleware to throw custom `UnauthorizedException` (which raises an HttpException with 403 response)
- The 403 response is backward compatible

## 2.7.2 - 2017-10-18

- refactor `PermissionRegistrar` to use `$gate-&amp;gt;before()`
- removed `log_registration_exception` as it is no longer relevant

## 2.7.1 - 2017-10-12

- fixed a bug where `Role`s and `Permission`s got detached when soft deleting a model

## 2.7.0 - 2017-09-27

- add support for L5.3

## 2.6.0 - 2017-09-10

- add `permission` scope

## 2.5.4 - 2017-09-07

- register the blade directives in the register method of the service provider

## 2.5.3 - 2017-09-07

- register the blade directives in the boot method of the service provider

## 2.5.2 - 2017-09-05

- let middleware use caching

## 2.5.1 - 2017-09-02

- add getRoleNames() method to return a collection of assigned roles

## 2.5.0 - 2017-08-30

- add compatibility with Laravel 5.5

## 2.4.2 - 2017-08-11

- automatically detach roles and permissions when a user gets deleted

## 2.4.1 - 2017-08-05

- fix processing of pipe symbols in `@hasanyrole` and `@hasallroles` Blade directives

## 2.4.0 -2017-08-05

- add `PermissionMiddleware` and `RoleMiddleware`

## 2.3.2 - 2017-07-28

- allow `hasAnyPermission` to take an array of permissions

## 2.3.1 - 2017-07-27

- fix commands not using custom models

## 2.3.0 - 2017-07-25

- add `create-permission` and `create-role` commands

## 2.2.0 - 2017-07-01

- `hasanyrole` and `hasallrole` can accept multiple roles

## 2.1.6 - 2017-06-06

- fixed a bug where `hasPermissionTo` wouldn't use the right guard name

## 2.1.5 - 2017-05-17

- fixed a bug that didn't allow you to assign a role or permission when using multiple guards

## 2.1.4 - 2017-05-10

- add `model_type` to the primary key of tables that use a polymorphic relationship

## 2.1.3 - 2017-04-21

- fixed a bug where the role()/permission() relation to user models would be saved incorrectly
- added users() relation on Permission and Role

## 2.1.2 - 2017-04-20

- fix a bug where the `role()`/`permission()` relation to user models would be saved incorrectly
- add `users()` relation on `Permission` and `Role`

## 2.0.2 - 2017-04-13

- check for duplicates when adding new roles and permissions

## 2.0.1 - 2017-04-11

- fix the order of the `foreignKey` and `relatedKey` in the relations

## 2.0.0 - 2017-04-10

- Requires minimum Laravel 5.4
- cache expiration is now configurable and set to one day by default
- roles and permissions can now be assigned to any model through the `HasRoles` trait
- removed deprecated `hasPermission` method
- renamed config file from `laravel-permission` to `permission`.

## 1.17.0 - 2018-08-24

- added support for Laravel 5.7

## 1.16.0 - 2018-02-07

- added support for Laravel 5.6

## 1.15 - 2017-12-08

- allow `hasAnyPermission` to take an array of permissions

## 1.14.1 - 2017-10-26

- fixed `Gate::before` for custom gate callbacks

## 1.14.0 - 2017-10-18

- refactor `PermissionRegistrar` to use `$gate-&amp;gt;before()`
- removed `log_registration_exception` as it is no longer relevant

## 1.13.0 - 2017-08-31

- added compatibility for Laravel 5.5

## 1.12.0

- made foreign key name to users table configurable

## 1.11.1

- `hasPermissionTo` uses the cache to avoid extra queries when it is called multiple times

## 1.11.0

- add `getDirectPermissions`, `getPermissionsViaRoles`, `getAllPermissions`

## 1.10.0 - 2017-02-22

- add `hasAnyPermission`

## 1.9.0 - 2017-02-20

- add `log_registration_exception` in settings file
- fix for ambiguous column name `id` when using the role scope

## 1.8.0 - 2017-02-09

- `hasDirectPermission` method is now public

## 1.7.0 - 2016-01-23

- added support for Laravel 5.4

## 1.6.1 - 2016-01-19

- make exception logging more verbose

## 1.6.0 - 2016-12-27

- added `Role` scope

## 1.5.3 - 2016-12-15

- moved some things to `boot` method in SP to solve some compatibility problems with other packages

## 1.5.2 - 2016-08-26

- make compatible with L5.3

## 1.5.1 - 2016-07-23

- fixes `givePermissionTo` and `assignRole` in Laravel 5.1

## 1.5.0 - 2016-07-23

** this version does not work in Laravel 5.1, please upgrade to version 1.5.1 of this package

- allowed `givePermissionTo` to accept multiple permissions
- allowed `assignRole` to accept multiple roles
- added `syncPermissions`-method
- added `syncRoles`-method
- dropped support for PHP 5.5 and HHVM

## 1.4.0 - 2016-05-08

- added `hasPermissionTo` function to the `Role` model

## 1.3.4 - 2016-02-27

- `hasAnyRole` can now properly process an array

## 1.3.3 - 2016-02-24

- `hasDirectPermission` can now accept a string

## 1.3.2 - 2016-02-23

- fixed user table configuration

## 1.3.1 - 2016-01-10

- fixed bug when testing for non existing permissions

## 1.3.0 - 2015-12-25

- added compatibility for Laravel 5.2

## 1.2.1 - 2015-12-22

- use database_path to publish migrations

## 1.2.0 - 2015-10-28

###Added

- support for custom models

## 1.1.0 - 2015-10-12

### Added

- Blade directives
- `hasAllRoles()`- and `hasAnyRole()`-functions

## 1.0.2 - 2015-10-11

### Fixed

- Fix for running phpunit locally

## 1.0.1 - 2015-09-30

### Fixed

- Fixed the inconsistent naming of the `hasPermission`-method.

## 1.0.0 - 2015-09-16

### Added

- Everything, initial release
