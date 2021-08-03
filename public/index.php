<?php
// globals
session_start();
define('BASE_DIR', realpath(__DIR__ . '/..'));
define('HTML_DIR', BASE_DIR . '/templates/site');
define('SRC_DIR', BASE_DIR . '/src');

// autoloader
include __DIR__ . '/../vendor/autoload.php';
use SimpleHtml\Common\View\Html;
use SimpleHtml\Common\Generic\Registry;

// grab config
$config = include SRC_DIR . '/config/config.php';

// init vars
$body = '';
$uri  = $_POST['uri'] ?? $_SERVER['REQUEST_URI'] ?? '';
$uri  = (strlen($uri) <= 1) ? '/home' : $uri;
$msg  = '';
// routes w/ forms need to do an include
header('Content-Type: text/html');
header('Content-Encoding: compress');

try {
    // add pre-processing logic in this file:
    include SRC_DIR . '/processing.php';
    $html = new Html($config, $uri, HTML_DIR);
    echo $html->render();
} catch (Throwable $t) {
    Registry::setItem('msg', $t->getMessage());
    $html = new Html($config, '/error', HTML_DIR);
    echo $html->render();
}
