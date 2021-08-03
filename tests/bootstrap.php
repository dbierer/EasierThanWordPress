<?php
// globals
session_start();
define('BASE_DIR', realpath(__DIR__ . '/..'));
define('HTML_DIR', BASE_DIR . '/templates/site');
define('SRC_DIR', BASE_DIR . '/src');

// autoloader
include __DIR__ . '/../vendor/autoload.php';
