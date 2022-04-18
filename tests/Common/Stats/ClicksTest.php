<?php
namespace FileCMSTest\Common\Stats;

use FileCMS\Common\Stats\Clicks;
use PHPUnit\Framework\TestCase;
class ClicksTest extends TestCase
{
    public $config = [];
    public $click_fn = '';
    public function setUp() : void
    {
        $this->config = include BASE_DIR . '/tests/config/test.config.php';
        $this->click_fn = __DIR__ . '/../../test_files/click_test.csv';
        if (file_exists($this->click_fn)) unlink($this->click_fn);
    }
    public function testAddReturnsTrueIfUrlIsSlash()
    {
        $url = '/';
        $expected = TRUE;
        $actual   = Clicks::add($url, $this->click_fn);
        $this->assertEquals($expected, $actual);
    }
    public function testAddCreatesCSVFile()
    {
        $url = '/test';
        Clicks::add($url, $this->click_fn);
        $expected = TRUE;
        $actual   = file_exists($this->click_fn);
        $this->assertEquals($expected, $actual);
    }
    public function testGetReturnsEmptyArrayIfClickFileDoesNotExist()
    {
        $expected = [];
        $actual   = Clicks::get($this->click_fn);
        $this->assertEquals($expected, $actual);
    }
    public function testGetReturnsCorrectArrayKey()
    {
        $date = date('Y-m-d');
        $url = '/test';
        Clicks::add($url, $this->click_fn);
        $expected = $url . '-' . $date;
        $actual   = array_keys(Clicks::get($this->click_fn))[0];
        $this->assertEquals($expected, $actual);
    }
    public function testGetReturnsCorrectUrl()
    {
        $date = date('Y-m-d');
        $url = '/test';
        Clicks::add($url, $this->click_fn);
        $get  = Clicks::get($this->click_fn);
        $value = current($get);
        $expected = $url;
        $actual   = $value['url'];
        $this->assertEquals($expected, $actual);
    }
    public function testGetReturnsCorrectHits()
    {
        $date = date('Y-m-d');
        $url = '/test';
        Clicks::add($url, $this->click_fn);
        Clicks::add($url, $this->click_fn);
        Clicks::add($url, $this->click_fn);
        $get  = Clicks::get($this->click_fn);
        $value = current($get);
        $expected = 3;
        $actual   = (int) $value['hits'];
        $this->assertEquals($expected, $actual);
    }
}
