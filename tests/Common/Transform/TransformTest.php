<?php
namespace FileCMSTest\Common\Transform;

use FileCMS\Common\Transform\Transform;
use PHPUnit\Framework\TestCase;
class TransformTest extends TestCase
{
    public $testFileDir = '';
    public function setUp() : void
    {
        $this->testFileDir = realpath(__DIR__ . '/../test_files');
    }
    public function testTransform()
    {
        $expected = 1;
        $actual   = 0;
        $this->assertEquals($expected, $actual);
    }
}
