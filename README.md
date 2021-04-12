# WebSK php-logger

## Config example
* config/config.default.php

## Demo
* copy config/config.default.php as config/config.php
* replace settings and paths
* composer install
* create MySQL DB db_logger (or other) 
* load in MySQL DB db_logger src/WebSK/Logger/dumps/db_logger.sql
* cd public
* php -S localhost:8000
* open http://localhost:8000