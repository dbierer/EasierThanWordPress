<?php
use FileCMS\Common\File\Upload;
use FileCMS\Common\Security\Profile;
use FileCMS\Common\Generic\Messages;
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
$upload = new Upload($config);
$response = $upload->handle('upload');
header('Content-type: application/json');
echo json_encode($response);

