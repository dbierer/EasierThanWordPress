<?php
session_start();
error_reporting(E_ALL);
define('BASE_DIR', __DIR__);
define('HTML_DIR', BASE_DIR . '/templates/site');
define('SRC_DIR', BASE_DIR . '/src');
ini_set('error_log', BASE_DIR . '/logs/error.log');
include BASE_DIR . '/vendor/autoload.php';
