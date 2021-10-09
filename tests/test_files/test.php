<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
$img_dir = __DIR__ . '/images';
$img_fn = __DIR__ . '/images/blog-1.jpg';
$thumb_dir = __DIR__ . '/thumb';
// grab extension
$ext = pathinfo($img_fn, PATHINFO_EXTENSION);
// create GD image
$func = 'imagecreatefromjpeg';
$image = $func($img_fn);
// scale to 50 x 50
$thumb = imagescale($image, 50);
// get thumb FN
$thumb_fn = str_replace($img_dir, $thumb_dir, $img_fn);
// save
$save = 'imagejpeg';
$save($thumb, $thumb_fn);
