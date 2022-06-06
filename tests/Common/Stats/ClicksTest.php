<?php
namespace FileCMSTest\Common\Stats;

use SplFileObject;
use FileCMS\Common\Stats\Clicks;
use PHPUnit\Framework\TestCase;
class ClicksTest extends TestCase
{
    public $config = [];
    public $click_fn = '';
    public function setUp() : void
    {
        $this->config = include BASE_DIR . '/tests/config/test.config.php';
        $this->click_fn = BASE_DIR . '/tests/logs/click_test.csv';
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
    public function testGet()
    {
        $url = '/test';
        Clicks::add($url, $this->click_fn);
        Clicks::add($url, $this->click_fn);
        Clicks::add($url, $this->click_fn);
        $get  = Clicks::get($this->click_fn);
        $expected = 3;
        $actual   = (int) current($get)['hits'];
        $this->assertEquals($expected, $actual);
    }
    public function testRawGetReturnsEmptyArrayIfClickFileDoesNotExist()
    {
        $callback = function ($val) { return $val[0]; };
        $expected = [];
        $actual   = Clicks::raw_get('/tmp/does_not_exist', $callback);
        $this->assertEquals($expected, $actual);
    }
    public function testGetByPageByDayReturnsCorrectArrayKey()
    {
        $date = date('Y-m-d');
        $url = '/test';
        Clicks::add($url, $this->click_fn);
        $expected = $url . '-' . $date;
        $actual   = array_keys(Clicks::get_by_page_by_day($this->click_fn))[0];
        $this->assertEquals($expected, $actual);
    }
    public function testGetByPageByDayReturnsCorrectUrl()
    {
        $date = date('Y-m-d');
        $url = '/test';
        Clicks::add($url, $this->click_fn);
        $get  = Clicks::get_by_page_by_day($this->click_fn);
        $value = current($get);
        $expected = $url;
        $actual   = $value['url'];
        $this->assertEquals($expected, $actual);
    }
    public function testGetByPageByDayReturnsCorrectHits()
    {
        $date = date('Y-m-d');
        $url = '/test';
        Clicks::add($url, $this->click_fn);
        Clicks::add($url, $this->click_fn);
        Clicks::add($url, $this->click_fn);
        $get  = Clicks::get_by_page_by_day($this->click_fn);
        $value = current($get);
        $expected = 3;
        $actual   = (int) $value['hits'];
        $this->assertEquals($expected, $actual);
    }
    public function testRawGetSortsByKey()
    {
        Clicks::add('/aaa', $this->click_fn);
        Clicks::add('/bbb', $this->click_fn);
        Clicks::add('/ccc', $this->click_fn);
        $expected = ['/aaa','/bbb','/ccc'];
        $callback = function ($val) { return $val[0]; };
        $actual   = array_keys(Clicks::raw_get($this->click_fn, $callback));
        $this->assertEquals($expected, $actual);
    }
    public function testRawGetRecordsHitsProperly()
    {
        $url = '/test';
        Clicks::add($url, $this->click_fn);
        Clicks::add($url, $this->click_fn);
        Clicks::add($url, $this->click_fn);
        $expected = 3;
        $callback = function ($val) { return $val[0]; };
        $actual   = Clicks::raw_get($this->click_fn, $callback)['/test']['hits'];
        $this->assertEquals($expected, $actual);
    }
    public function testRawGetSkipsEmptyRows()
    {
        $url = '/test';
        Clicks::add($url, $this->click_fn);
        $obj = new SplFileObject($this->click_fn, 'a');
        $ok = (bool) $obj->fputcsv(['/test', date('Y-m-d')]);
        unset($obj);
        Clicks::add($url, $this->click_fn);
        $expected = 2;
        $callback = function ($val) { return $val[0]; };
        $actual   = Clicks::raw_get($this->click_fn, $callback)['/test']['hits'];
        $this->assertEquals($expected, $actual);
    }
    public function testRawGetCreatesDiscrepanciesArrayIfNumColsDoesNotMatchHeaders()
    {
        $url = '/test';
        Clicks::add($url, $this->click_fn);
        $obj = new SplFileObject($this->click_fn, 'a');
        $ok = (bool) $obj->fputcsv(['/test', date('Y-m-d')]);
        unset($obj);
        Clicks::add($url, $this->click_fn);
        $callback = function ($val) { return $val[0]; };
        Clicks::raw_get($this->click_fn, $callback);
        $expected = 1;
        $actual   = count(Clicks::$discrepancies);
        $this->assertEquals($expected, $actual);
    }
    public function testGetByPath()
    {
        Clicks::add('/aaa', $this->click_fn);
        Clicks::add('/bbb/111', $this->click_fn);
        Clicks::add('/bbb/222', $this->click_fn);
        Clicks::add('/ccc/111', $this->click_fn);
        Clicks::add('/ccc/222', $this->click_fn);
        Clicks::add('/ccc/333', $this->click_fn);
        $expected = 3;
        $actual   = count(Clicks::get_by_path($this->click_fn, '/ccc/'));
        $this->assertEquals($expected, $actual);
    }
}
