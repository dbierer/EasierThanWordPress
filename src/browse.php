<?php
use FileCMS\Common\File\Browse;
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
$browse = new Browse($config);
$generator = $browse->handle();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browsing Files</title>
</head>
<body>
    <?php foreach ($generator as $html) echo $html; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.slim.min.js"></script>
    <script>
        // Helper function to get parameters from the query string.
        function getUrlParam( paramName ) {
            var reParam = new RegExp( '(?:[\?&]|&)' + paramName + '=([^&]+)', 'i' );
            var match = window.location.search.match( reParam );

            return ( match && match.length > 1 ) ? match[1] : null;
        }
        // Simulate user action of selecting a file to be returned to CKEditor.
        function returnFileUrl(id) {
            var funcNum = getUrlParam( 'CKEditorFuncNum' );
            var fileUrl = $('#' + id).val();
            window.opener.CKEDITOR.tools.callFunction( funcNum, fileUrl );
            window.close();
        }
    </script>
</body>
</html>

