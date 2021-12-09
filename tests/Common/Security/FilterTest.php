<?php
namespace FileCMSTest\Common\Security;

use FileCMS\Common\Security\Filter;
use PHPUnit\Framework\TestCase;
class FilterTest extends TestCase
{
    public function testTrim()
    {
        $text = "\n" . 'TEST ';
        $expected = 'TEST';
        $actual   = Filter::trim($text, []);
        $this->assertEquals($expected, $actual);
    }
    public function testStripTags()
    {
        $text = '<p>TEST<script>BAD</script></p>';
        $expected = 'TESTBAD';
        $actual   = Filter::stripTags($text, []);
        $this->assertEquals($expected, $actual);
    }
    public function testTruncate()
    {
        $text = 'TEST0000';
        $expected = 'TEST';
        $actual   = Filter::truncate($text, ['length' => 4]);
        $this->assertEquals($expected, $actual);
    }
    public function testTruncateWorksIfNoLength()
    {
        $text = 'TEST0000';
        $expected = $text;
        $actual   = Filter::truncate($text);
        $this->assertEquals($expected, $actual);
    }
    public function testTruncateWorksIfLengthTooLong()
    {
        $text = 'TEST0000';
        $expected = $text;
        $actual   = Filter::truncate($text, ['length' => 64]);
        $this->assertEquals($expected, $actual);
    }
    public function testDate()
    {
        $text = '';
        $expected = date(Filter::DEFAULT_DATE);
        $actual   = Filter::date($text, []);
        $this->assertEquals($expected, $actual);
    }
    public function testDateWithDifferentFormat()
    {
        $text = '';
        $fmt  = 'l, d M Y';
        $expected = date($fmt);
        $actual   = Filter::date($text, ['format' => $fmt]);
        $this->assertEquals($expected, $actual);
    }
    public function testRunFilters()
    {
        $callbacks = [
            'trim'      => [],
            'stripTags' => [],
            'truncate'  => ['length' => 4],
        ];
        $text = "\n" . 'TEST0000 ';
        $expected = 'TEST';
        $actual   = Filter::runFilters($text, $callbacks);
        $this->assertEquals($expected, $actual);
    }
}
