<?php
// add pre-processing logic based on URL here:

switch (TRUE) {
    case (strpos($uri, '/super/upload') !== FALSE) :
        require __DIR__ . '/upload.php';
        exit;
    case (strpos($uri, '/super/browse') !== FALSE) :
        require __DIR__ . '/browse.php';
        exit;
    default :
        // fall through
}
