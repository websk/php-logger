# WebSK php-logger

`websk/php-logger` stores changes to WebSK entities and provides Slim routes for browsing object history and comparing revisions.

## Requirements

- PHP 8.3, 8.4, or 8.5
- Composer 2
- Node.js 20 and npm 10 when building the demo frontend assets
- `ext-mbstring` and `ext-pdo_mysql`
- MySQL
- one cache backend supported by `websk/php-cache` and its PHP extension or client package

The bundled demo configuration uses the `WebSK\Cache\Engines\Memcached` backend. It requires two separate components: the PHP `memcached` extension, which provides the `Memcached` class, and a running Memcached server. Redis and Memcache can be selected when their corresponding runtime dependencies are installed.

On macOS with Homebrew PHP, install and start both components with:

```shell
brew install libmemcached memcached
pecl install memcached
brew services start memcached
```

When PECL asks for `zlib directory [no]`, enter the path printed by `brew --prefix zlib`. The defaults can be accepted for the other optional features.

If PECL does not enable the extension automatically, add it to the active PHP configuration shown by `php --ini`:

```shell
echo 'extension=memcached.so' > "$(brew --prefix)/etc/php/8.5/conf.d/ext-memcached.ini"
```

Restart an already running PHP server after enabling the extension. Verify both components before starting the demo:

```shell
php -r "var_dump(class_exists('Memcached'));"
printf 'version\r\n' | nc 127.0.0.1 11211
```

The first command must print `bool(true)`, and the second must return the Memcached server version. The error `Class "Memcached" not found` means the PHP extension is missing or is enabled for a different PHP installation; a running Memcached service alone does not provide this class.

On Debian or Ubuntu, the equivalent packages are commonly installed with `sudo apt install php-memcached memcached`, followed by `sudo systemctl enable --now memcached`.

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
npm ci
npm run build
php -S localhost:8000 -t public
```

Open <http://localhost:8000>.

## Frontend assets

The demo layout and the HTML comparison renderer use files below `/assets/libraries/`. These are absolute browser URLs, so the application web server must expose its generated asset directory as `/assets`. In this repository, `public/` is the document root and the files are generated in `public/assets/libraries/`.

For a source checkout, install the locked npm dependencies and build the assets before starting the application:

```shell
npm ci
npm run build
```

The layout loads the following assets:

| Source | Public path |
| --- | --- |
| `bootstrap/dist/css/bootstrap.min.css` and its source map | `/assets/libraries/bootstrap/css/` |
| `bootstrap/dist/js/bootstrap.bundle.min.js` and its source map | `/assets/libraries/bootstrap/js/` |
| `jquery/dist/jquery.min.js` | `/assets/libraries/jquery/jquery.min.js` |
| `jquery-ui/dist/jquery-ui.min.js` | `/assets/libraries/jquery-ui/jquery-ui.min.js` |
| `jquery-ui/dist/themes/base/jquery-ui.min.css` and `images/` | `/assets/libraries/jquery-ui/themes/base/` |
| `jquery-validation/dist/jquery.validate.min.js` | `/assets/libraries/jquery-validation/jquery.validate.min.js` |
| `@fortawesome/fontawesome-free/css/` and `webfonts/` | `/assets/libraries/font-awesome/` |
| `vendor/websk/php-crud/resources/css/crud.css` | `/assets/libraries/crud/crud.css` |
| `resources/css/logger.css` | `/assets/libraries/logger/logger.css` |

The bundled `views/layouts/layout.main.tpl.php` emits the corresponding `<link>` and `<script>` tags. Applications using this layout do not need to add the tags again; the files only need to be available at the documented URLs.

`CompareHTML` additionally injects these local resources when the first comparison is rendered:

| Source | Public path |
| --- | --- |
| `highlight.js/styles/github.css` | `/assets/libraries/highlight.js/styles/github.css` |
| `diff2html/bundles/css/diff2html.min.css` | `/assets/libraries/diff2html/css/diff2html.min.css` |
| `diff2html/bundles/js/diff2html-ui.min.js` | `/assets/libraries/diff2html/js/diff2html-ui.min.js` |
| `diff/dist/diff.min.js` | `/assets/libraries/diff/diff.min.js` |

The comparison renderer emits these tags once per PHP request, before its initialization script. A custom layout should therefore publish the files at these paths instead of adding duplicate tags.

The generated `public/assets` directory is excluded from Git and Composer archives. Applications that install `websk/php-logger` through Composer must publish the same files into their own public directory as part of their frontend build. Use `scripts/build-assets.mjs` as the canonical copy manifest; project styles are available at `vendor/websk/php-logger/resources/css/logger.css` and `vendor/websk/php-crud/resources/css/crud.css` in a consuming application.

Run `npm run check:assets` after building to verify that the published files exactly match the locked dependencies and project stylesheets.

## Serialized entity data

The logger restores serialized application entities to display historical field values. The logger database must therefore be treated as trusted input. Do not import serialized values from untrusted sources. Invalid serialized records are rejected and rendered as unavailable history.

## Development

Install development dependencies and run all checks:

```shell
composer install
composer check
composer audit --locked
npm ci
npm run build
npm run check:assets
npm audit --omit=dev --audit-level=moderate
```

The Composer `check` script runs PHP syntax checks, PHPUnit, and PHPStan at level `max`. The npm check verifies that the locally published Bootstrap 5, CRUD, and comparison-view assets exactly match the locked dependencies and project stylesheets.

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
