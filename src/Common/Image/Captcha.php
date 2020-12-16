<?php
namespace Common\Image;
/**
 * Creates a CAPTCHA
 */
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Common\Image\SingleChar;
use Common\Image\Strategy\ {LineFill,DotFill,Shadow,RotateText};
class Captcha
{
	const NUM_BYTES = 2;
	const FONT_FILE = SRC_DIR . '/fonts/FreeSansBold.ttf';
	const IMG_DIR   = BASE_DIR . '/public/img/captcha';
	public $token   = '';
	public $phrase  = '';
	public $images  = [];
	public $strategies = ['rotate', 'line', 'line', 'dot', 'dot', 'shadow'];
	/**
	 * Writes out NUM_BYTES * 2 CAPTCHA images
	 *
	 * @param string $token : used to identify this user
	 * @return array $images : filenames of CAPTCHA images produced
	 */
	public function writeImages(string $token)
	{
		// generate random hex number for CAPTCHA
		$phrase = strtoupper(bin2hex(random_bytes(self::NUM_BYTES)));
		$length = strlen($phrase);
		$images = [];
		for ($x = 0; $x < $length; $x++) {
			$char = new SingleChar($phrase[$x], self::FONT_FILE);
			$char->writeFill();
			shuffle($this->strategies);
			foreach ($this->strategies as $item) {
				switch ($item) {
					case 'rotate' :
						RotateText::writeText($char, -25, 25);
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
						$num = rand(1, 8);
						$red = rand(0x70, 0xEF);
						$green = rand(0x70, 0xEF);
						$blue = rand(0x70, 0xEF);
						Shadow::writeText($char, $num, $red, $green, $blue);
						break;
					default :
						// do nothing
				}
			}
			$char->writeText();
			$fn = $x . '_' . $token . '.png';
			$char->save(self::IMG_DIR . '/' . $fn);
			$this->images[] = $fn;
		}
		$this->phrase = $phrase;
		return $this->images;
	}
	/**
	 * Erase images older than 1 day
	 */
	public function __destruct()
	{
		$iter = new \RecursiveDirectoryIterator(self::IMG_DIR);
		$now = time();
		$yesterday = $now - (60 * 60 * 24);
		foreach ($iter as $name => $obj) {
			// find files older than 24 hours
			if ($obj->isFile() && $obj->getCTime() < $yesterday) {
				unlink($name);
			}
		}
	}
}
