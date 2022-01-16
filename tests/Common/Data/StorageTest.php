<?php
namespace FileCMSTest\Common\Data;

use FileCMS\Common\Data\Storage;
class StorageTest extends StorageBase
{
    public function testConstructCreatesConfigProperly()
    {
        $expected = $this->config['STORAGE'];
        $actual   = $this->storage->config;
        $this->assertEquals($expected, $actual);
    }
    public function testFail()
    {
        $expected = 1;
        $actual   = 0;
        $this->assertEquals($expected, $actual);
    }
}
