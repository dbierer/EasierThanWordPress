<?php
namespace FileCMSTest\Common\Data\Json;

use FileCMS\Common\Data\Json\Json;
use FileCMSTest\Common\Data\StorageBase;
class JsonTest extends StorageBase
{
    public function testSaveJsonString()
    {
        $data = ['A' => 111, 'B' => 222, 'C' => 333];
        Json::save(self::$tmpFn, $data);
        $expected = $data;
        $actual   = json_decode(file_get_contents(self::$tmpFn), TRUE);
        $this->assertEquals($expected, $actual);
    }
    public function testFetchReturnsPhpArray()
    {
        $data = ['A' => 111, 'B' => 222, 'C' => 333];
        Json::save(self::$tmpFn, $data);
        $expected = $data;
        $actual   = Json::fetch(self::$tmpFn)[0];
        $this->assertEquals($expected, $actual);
    }
    public function testFetchReturnsStdClass()
    {
        $data = ['A' => 111, 'B' => 222, 'C' => 333];
        Json::save(self::$tmpFn, $data);
        $result   = Json::fetch(self::$tmpFn,FALSE);
        $expected = 'stdClass';
        $actual   = get_class($result[0]);
        $this->assertEquals($expected, $actual);
    }
    public function testSavesMultipleJsonStrings()
    {
        $data = ['A' => 111, 'B' => 222, 'C' => 333];
        Json::save(self::$tmpFn, $data);
        Json::save(self::$tmpFn, $data);
        $expected = [$data, $data];
        $actual   = Json::fetch(self::$tmpFn);
        $this->assertEquals($expected, $actual);
    }
    public function testDoesNotSaveMultipleJsonStringsIfNotAppend()
    {
        $data = ['A' => 111, 'B' => 222, 'C' => 333];
        Json::save(self::$tmpFn, $data);
        Json::save(self::$tmpFn, $data, FALSE);
        $expected = $data;
        $actual   = Json::fetch(self::$tmpFn)[0];
        $this->assertEquals($expected, $actual);
    }
    public function testFetchErasesStorage()
    {
        $data = ['A' => 111, 'B' => 222, 'C' => 333];
        Json::save(self::$tmpFn, $data);
        Json::fetch(self::$tmpFn, TRUE, TRUE);
        $expected = FALSE;
        $actual   = file_exists(self::$tmpFn);
        $this->assertEquals($expected, $actual);
    }
}
