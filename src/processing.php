<?php
// add pre-processing logic based on URL here:

switch (TRUE) {
    // example:
    case (strpos($uri, '/super/upload') !== FALSE) :
        require __DIR__ . '/upload.php';
        exit;
    default :
        // fall through
}
