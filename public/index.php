<?php

define('APP_RUN_START_TIME',microtime(true));

require __DIR__.'/../vendor/autoload.php';

require __DIR__.'/../bootstrap/app.php';

bootstrap\App::run();
