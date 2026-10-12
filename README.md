# WebSK php-logger

`websk/php-logger` stores changes to WebSK entities and provides Slim routes for browsing object history and comparing revisions.

## Requirements

- PHP 8.3, 8.4, or 8.5; PHP 8.5.11 is used for the release checks
- Composer 2
- `ext-mbstring` and `ext-pdo_mysql`
- MySQL
- one cache backend supported by `websk/php-cache` and its PHP extension or client package

The bundled demo configuration uses Memcached. Install the `memcached` PHP extension and run a Memcached server before using that configuration. Redis and Memcache can be selected when their corresponding runtime dependencies are installed.

## Installation

```shell
composer require websk/php-logger
```

## Configuration

Copy `config/config.default.php` to `config/config.php`, then update the database, cache, URL, and filesystem settings. The local configuration file is ignored by Git.

The user link integration uses two independent settings:

- `logger.user_service_container_name` is the PSR-11 identifier of the user entity service;
- `logger.user_profile_route_name` is the named Slim route used to create profile links.

Either setting can be left empty when user profile links are not required. `user_full_id` itself is nullable.

## Database

Create the configured MySQL database and apply `dumps/db_logger.sql` through the WebSK DB migration command:

```shell
php vendor/bin/websk_db_migration.php migrations:migration_auto
```

For interactive migration handling, use:

```shell
php vendor/bin/websk_db_migration.php migrations:migration_handle
```

The migration normalizes existing nullable text values before applying the non-null constraints expected by the typed `LoggerEntry` entity. It also adds an `(object_full_id, id)` index for previous-revision lookups.

## Demo

After configuring the database and cache backend:

```shell
composer install
php -S localhost:8000 -t public
```

Open <http://localhost:8000>.

## Serialized entity data

The logger restores serialized application entities to display historical field values. The logger database must therefore be treated as trusted input. Do not import serialized values from untrusted sources. Invalid serialized records are rejected and rendered as unavailable history.

## Development

Install development dependencies and run all checks:

```shell
composer install
composer check
composer audit --locked
```

The `check` script runs PHP syntax checks, PHPUnit, and PHPStan at level `max`.

The optional MySQL integration test expects an already migrated disposable database:

```shell
MYSQL_TEST_HOST=127.0.0.1 \
MYSQL_TEST_DATABASE=db_logger \
MYSQL_TEST_USER=root \
MYSQL_TEST_PASSWORD=secret \
composer test:mysql
```

Before packaging a release, also run:

```shell
composer validate --strict
composer check-platform-reqs
composer dump-autoload --optimize --strict-psr
composer archive
```

## License

MIT. See [LICENSE](LICENSE).
