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
        Transform::$container = [];
    }
    public function testGetInstanceReturnsNullIfClassEmpty()
    {
        $expected = NULL;
        $actual   = Transform::get_instance('');
        $this->assertEquals($expected, $actual);
    }
    public function testGetInstanceReturnsExpectedInstance()
    {
        $expected = 'ArrayObject';
        $obj      = Transform::get_instance('ArrayObject');
        $actual   = get_class($obj);
        $this->assertEquals($expected, $actual);
    }
    public function testGetInstancePopulatesContainer()
    {
        $expected = 1;
        $obj = Transform::get_instance('ArrayObject');
        $actual = count(Transform::$container);
        $this->assertEquals($expected, $actual);
    }
    public function testLoadTransformsPopulatesContainerWithExpectedNumber()
    {
        $path = __DIR__ . '/../../../src/Transform';
        $expected = count(glob($path . '/*.php'));
        $actual = Transform::load_transforms($path);
        $this->assertEquals($expected, $actual);
    }
}
