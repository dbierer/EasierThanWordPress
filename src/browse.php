<?php
use SimpleHtml\Common\File\Browse;
use SimpleHtml\Common\Security\Profile;
use SimpleHtml\Common\Generic\Messages;
// process contact post (if any)
// $OBJ == calling instance (usually from /public/index.php)
if (!empty($OBJ)) {
    $uri    = $OBJ->uri;
    $config = $OBJ->config;
}
// check to see if authenticated
$message  = Messages::getInstance();
if (Profile::verify($config) === FALSE) {
    Profile::logout();
    $message->addMessage('Unable to authenticate');
    header('Location: /');
    exit;
}
$browse = new Browse($config);
$response = $browse->handle();
header('Content-type: application/json');
echo json_encode($response);

