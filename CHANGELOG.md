# Changelog

All notable changes to this project are documented in this file.

The release history was reconstructed from remote Git tags, GitHub Releases, and the corresponding commit diffs. Dates are GitHub Release publication dates where a release exists; otherwise, the tagged commit date is used.

## [Unreleased]

## [3.0.0] - 2026-10-12

### Added

- Added an MIT license and Composer license metadata.
- Added PHPUnit 12 tests for comparison rendering, logger configuration, entry persistence, and request handlers, plus an optional MySQL integration suite.
- Added PHPStan 2 at level `max`, syntax checks, and a combined `composer check` command.
- Added an explicit `ext-pdo_mysql` runtime requirement and package archive exclusions.
- Added database migration steps that normalize nullable legacy values and create an `(object_full_id, id)` history lookup index.

### Changed

- Verified compatibility with PHP 8.5 while retaining the `^8.3` platform constraint for PHP 8.3, 8.4, and 8.5 consumers.
- Updated Slim to 4.15 and the WebSK stack to `php-config` 2.2, `php-db` 3.0, `php-crud` 3.0, `php-entity` 3.0, `php-slim` 2.4, `php-utils` 3.0, and `php-view` 3.1.
- Replaced the root namespace autoloader with an explicit `WebSK\\` PSR-4 mapping.
- Updated dependency injection, route registration, entity lookup, and rendering code for the current PHP-DI, Slim, and WebSK APIs.
- Expanded installation, configuration, database migration, development, and trusted serialized-data documentation.

### Fixed

- Fixed nullable and typed-value handling for PHP 8.5.
- Fixed user service and profile route configuration so they can be configured independently or omitted.
- Fixed object history lookup and handling of missing previous revisions.
- Escaped values passed to generated HTML and JavaScript and rejected invalid serialized history records.

## [2.1.2] - 2026-05-20

### Fixed

- Changed demo redirects from HTTP 302 to HTTP 307 so the request method and body are preserved.

## [2.1.1] - 2025-04-01

### Changed

- Changed the `serialized_object` database column to `MEDIUMTEXT`.
- Reused one serialized entity value for both insert and update paths.

## [2.1.0] - 2025-03-26

### Changed

- Updated `websk/php-db` from 2.0 to 2.1, `websk/php-crud` from 2.1 to 2.3, `websk/php-slim` from 2.2 to 2.3, `websk/php-utils` from 2.0 to 2.1, and `websk/php-view` from 2.2 to 2.3.

## [2.0.0] - 2025-03-24

### Changed

- Raised the minimum PHP version from 7.4 to 8.3.
- Migrated the demo and request pipeline from Slim 3 to Slim 4.14 with PHP-DI container integration.
- Updated the WebSK dependencies to their PHP 8 generation and removed the direct `websk/php-auth` dependency.
- Added typed class constants, return types, and PHP 8 compatible service definitions.
- Updated the installation documentation for Composer.

## [1.3.0] - 2022-02-13

### Changed

- Updated the WebSK dependency set, including `php-auth` 2.2, `php-crud` 1.4, `php-entity` 1.2, `php-slim` 1.1, and `php-view` 1.1.
- Refreshed transitive dependencies in the Composer lock file.

## [1.2.2] - 2021-07-22

### Changed

- Replaced Slim-specific request and response type declarations in the demo and request handlers with PSR-7 interfaces.
- Refreshed the Composer lock file.

## [1.2.1] - 2021-05-17

### Added

- Added the authentication database dump path to the demo database configuration.
- Documented the interactive database migration command.

### Changed

- Refreshed authentication-related locked dependencies.

## [1.2.0] - 2021-04-19

### Added

- Added side-by-side entity revision comparison rendering.
- Added request URI, user agent, and object history data used by logger views and filters.
- Added configurable database dump paths for migrations.

### Changed

- Moved the logger schema dump to `dumps/db_logger.sql`.
- Reworked logger entities, repositories, services, routes, and request handlers around revision comparison and object history.
- Updated database and related WebSK dependencies.

## [1.1.4] - 2021-04-19

- Published as an additional tag for the same commit as `1.2.0`; it contains no separate changes.

## [1.1.3] - 2021-02-07

### Changed

- Updated the demo application and the `websk/php-crud` dependency.

## [1.1.2] - 2021-02-07

### Changed

- Added PHP 7.4 as the supported runtime and updated the dependency set.
- Reorganized the demo application namespace and updated its configuration names.

## [1.1.1] - 2019-09-19

### Fixed

- Corrected Composer package metadata.

## [1.1.0] - 2019-09-19

### Added

- Added CRUD integration and an expanded demo application with configuration, layout, and database schema files.
- Added initial installation and demo instructions.

## [1.0.0] - 2019-02-20

### Added

- Initial release of the entity logger, including logger entities, repository and service classes, routes, request handlers, rendering, demo bootstrap, and schema dump.

[Unreleased]: https://github.com/websk/php-logger/compare/3.0.0...HEAD
[3.0.0]: https://github.com/websk/php-logger/compare/2.1.2...3.0.0
[2.1.2]: https://github.com/websk/php-logger/compare/2.1.1...2.1.2
[2.1.1]: https://github.com/websk/php-logger/compare/2.1.0...2.1.1
[2.1.0]: https://github.com/websk/php-logger/compare/2.0.0...2.1.0
[2.0.0]: https://github.com/websk/php-logger/compare/1.3.0...2.0.0
[1.3.0]: https://github.com/websk/php-logger/compare/1.2.2...1.3.0
[1.2.2]: https://github.com/websk/php-logger/compare/1.2.1...1.2.2
[1.2.1]: https://github.com/websk/php-logger/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/websk/php-logger/compare/1.1.3...1.2.0
[1.1.4]: https://github.com/websk/php-logger/releases/tag/1.1.4
[1.1.3]: https://github.com/websk/php-logger/compare/1.1.2...1.1.3
[1.1.2]: https://github.com/websk/php-logger/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/websk/php-logger/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/websk/php-logger/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/websk/php-logger/releases/tag/1.0.0
