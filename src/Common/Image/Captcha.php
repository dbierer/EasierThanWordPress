<?php
namespace SimpleHtml\Common\Image;
/**
 * Creates a CAPTCHA
 */
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SimpleHtml\Common\Image\SingleChar;
use SimpleHtml\Common\Image\Strategy\ {LineFill,DotFill,Shadow,RotateText};
class Captcha
{
    public const DEFAULT_FONT_FILE = __DIR__ . '/../../fonts/FreeSansBold.ttf';
    public const DEFAULT_IMG_DIR   = __DIR__ . '/../../../public/img/captcha';
    public const DEFAULT_NUM_BYTES = 4;
    public static $old_files = 360;     // # seconds old CAPTCHA files can remain
    public static $num_bytes = 4;
    public static $font_file = '';
    public static $img_dir   = '';
    public static $min = 1000;  // used if only numbers
    public static $max = 9999;  // used if only numbers
    public static $strategies = ['rotate', 'dot', 'line', 'shadow', 'line', 'dot', 'shadow'];
    public $token   = '';
    public $phrase  = '';
    public $images  = [];
    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        self::$font_file = $config['font_file'] ?? static::DEFAULT_FONT_FILE;
        self::$img_dir   = $config['img_dir']   ?? static::DEFAULT_IMG_DIR;
        self::$num_bytes = $config['num_bytes'] ?? static::DEFAULT_NUM_BYTES;
    }
    /**
     * Writes out $num_bytes * 2 CAPTCHA images
     *
     * @param string $token  : used to identify this user
     * @param bool $numbers  : numbers only
     * @return array $images : filenames of CAPTCHA images produced
     */
    public function writeImages(string $token, bool $numbers = TRUE)
    {
        // generate random hex number for CAPTCHA
        if ($numbers) {
            $phrase = (string) rand(static::$min, static::$max);
        } else {
            $phrase = strtoupper(bin2hex(random_bytes(static::$num_bytes)));
        }
        $length = strlen($phrase);
        $images = [];
        for ($x = 0; $x < $length; $x++) {
            $char = new SingleChar($phrase[$x], static::$font_file);
            $char->writeFill();
            shuffle(static::$strategies);
            foreach (self::$strategies as $item) {
                switch ($item) {
                    case 'rotate' :
                        RotateText::writeText($char, -40, 40);
                        break;
                    case 'line' :
                        $num = rand(1, 20);
                        LineFill::writeFill($char, $num);
                        break;
                    case 'dot' :
                        $num = rand(10, 40);
                        DotFill::writeFill($char, $num);
                        break;
                    case 'shadow' :
                        $num   = rand(1, 8);
                        $red   = rand(0x70, 0xEF);
                        $green = rand(0x70, 0xEF);
                        $blue  = rand(0x70, 0xEF);
                        Shadow::writeText($char, $num, $red, $green, $blue);
                        break;
                    default :
                        // do nothing
                }
            }
            $char->writeText();
            $fn = $x . '_' . $token . '.png';
            $char->save(static::$img_dir . '/' . $fn);
            $this->images[] = $fn;
        }
        $this->phrase = $phrase;
        return $this->images;
    }
    /**
     * Erase images older than self::$old_files number of seconds
     */
    public function __destruct()
    {
        $iter = new \RecursiveDirectoryIterator(static::$img_dir);
        $now = time();
        $expired = $now - self::$old_files;
        foreach ($iter as $name => $obj) {
            // find files older than 24 hours
            if ($obj->isFile() && $obj->getCTime() < $expired) {
                unlink($name);
            }
        }
    }
}
