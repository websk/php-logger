# WebSK php-logger

## Install

https://packagist.org/packages/websk/php-logger

install dependency using Composer

```shell
composer require websk/php-logger
```

## Config example
* config/config.default.php

## Demo
* copy config/config.default.php as config/config.php
* replace settings and paths
* composer install
* create MySQL DB db_logger (or other) 
* process migration in MySQL DB: `php vendor\bin\websk_db_migration.php migrations:migration_auto` or `php vendor\bin\websk_db_migration.php migrations:migration_handle`
* cd public
* php -S localhost:8000
* open http://localhost:8000