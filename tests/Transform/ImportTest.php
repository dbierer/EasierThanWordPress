<?php
namespace SimpleHtmlTest\Transform;

use SimpleHtml\Transform\Import;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
class ImportTest extends TestCase
{
    public function testGetDelimitedStartDoesNotExist()
    {
        $text     = '<html><body><p>xxx</p></body></html>';
        $start    = '<div>';
        $expected = $text;
        $actual = Import::get_delimited($text, $start);
        $this->assertEquals($expected, $actual, 'Contents from between delimiters not returned when start does not exist');
    }
    public function testGetDelimitedNoStop()
    {
        $text     = '<html><body><p>xxx</p></body></html>';
        $start    = '<body>';
        $expected = '<p>xxx</p></body></html>';
        $actual = Import::get_delimited($text, $start);
        $this->assertEquals($expected, $actual, 'Contents from between delimiters not returned with no stop');
    }
    public function testExtract()
    {
        $text     = '<html><body><p>xxx</p></body></html>';
        $start    = '<body>';
        $stop     = '</body>';
        $expected = '<p>xxx</p>';
        $actual = Import::get_delimited($text, $start, $stop);
        $this->assertEquals($expected, $actual, 'Contents from between delimiters not returned.');
    }
}
