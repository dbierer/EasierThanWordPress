<?php
namespace EasierThanWordPress\Common\Image\Strategy;
// https://www.php.net/manual/en/function.imagettftext.php
/**
 * Writes plain text to image
 */
use EasierThanWordPress\Common\Image\SingleChar;
class RotateText
{
    const MIN_ANGLE = -20;
    const MAX_ANGLE = 20;
    /**
     * Adjusts angle of text
     *
     * @param SingleChar $char
     * @param int $left : leftwards rotation (usually < 0)
     * @param int $right : rightwards rotation (usually > 0)
     */
    public static function writeText(
        SingleChar $char,
        int $left = self::MIN_ANGLE,
        int $right = self::MAX_ANGLE) : void
    {
        $char->angle = rand($left, $right);
        $char->angle = ($char->angle < 0) ? $char->angle + 360 : $char->angle;
    }
    /**
     * If angle = 90, y = 0;
     * If angle = 180, y = 45
     * If angle = 270, y = 45
     */
}
