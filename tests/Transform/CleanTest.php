<?php
namespace FileCMSTest\Transform;

use FileCMS\Common\Transform\TransformInterface;
use FileCMS\Transform\Clean;
use PHPUnit\Framework\TestCase;
class CleanTest extends TestCase
{
    public $clean = NULL;
    public function testImplementsTransformInterface()
    {
        $expected = TRUE;
        $clean = new Clean();
        $actual = ($clean instanceof TransformInterface);
        $this->assertEquals($expected, $actual, 'Class does not implement TransformInterface');
    }
    public function testFullHtmlReturnedIfTidyAvailable()
    {
        $str = 'TEST';
        $search = '<!DOCTYPE html>';
        $html = (new Clean())($str, ['bodyOnly' => FALSE]);
        $actual = (strpos($html, $search) === 0);
        if (class_exists('tidy')) {
            $expected = TRUE;
        } else {
            $expected = FALSE;
        }
        $this->assertEquals($expected, $actual, 'Full HTML document not returned');
    }
    public function testOnlyBodyContentsReturned()
    {
        $str = 'TEST';
        $expected = $str;
        $actual = (new Clean())($str, ['bodyOnly' => TRUE]);
        $this->assertEquals($expected, $actual, 'Body contents not returned');
    }
    public function testStripsOffLFandSpacesIfTidyAvailable()
    {
        $str = '<h1>Test</h1>' . "\n" . '<p>TEST</p>  ';
        if (class_exists('tidy')) {
            $expected = '<h1>Test</h1><p>TEST</p>';
        } else {
            $expected = $str;
        }
        $actual = (new Clean())($str);
        $this->assertEquals($expected, $actual, 'LF and leading/trailing spaces not removed.');
    }
}
