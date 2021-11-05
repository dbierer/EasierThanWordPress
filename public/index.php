<?php
require __DIR__ . '/../bootstrap.php';
use FileCMS\Common\View\Html;
use FileCMS\Common\Generic\Messages;

// grab config
$config = include SRC_DIR . '/config/config.php';

// init vars
$body = '';
$uri  = $_POST['uri'] ?? $_SERVER['REQUEST_URI'] ?? '';
$uri  = parse_url($uri,  PHP_URL_PATH);
$uri  = (strlen($uri) <= 1) ? '/home' : $uri;

try {
    // pre-processing logic:
    include SRC_DIR . '/processing.php';
    // routes w/ forms need to do an include
    header('Content-Type: text/html');
    header('Content-Encoding: compress');
    $html = new Html($config, $uri, HTML_DIR);
    echo $html->render();
} catch (Throwable $t) {
    (Messages::getInstance())->addMessage($t->getMessage());
    $html = new Html($config, '/error', HTML_DIR);
    echo $html->render();
}
