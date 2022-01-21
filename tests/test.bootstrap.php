<?php
define('BASE_DIR', realpath(__DIR__ . '/..'));
define('HTML_DIR', BASE_DIR . '/tests/test_files');
define('SRC_DIR', BASE_DIR . '/src');
error_reporting(E_ALL);
ini_set('display_errors', 1);   // change this to "1" during website development
ini_set('error_log', BASE_DIR . '/tests/logs/error.log');
require BASE_DIR . '/vendor/autoload.php';
session_start();
