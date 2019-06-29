<?php
session_start();

define('APP_RUN_START_TIME',microtime(true));

require __DIR__.'/../vendor/autoload.php';

bootstrap\App::run();
