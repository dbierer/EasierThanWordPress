<?php
namespace EasierThanWordPressTest\Common\View;

use EasierThanWordPress\Common\Generic\Registry;
use PHPUnit\Framework\TestCase;
class RegistryTest extends TestCase
{
    public function testSetAndGetItem()
    {
        Registry::setItem('test', 'TEST');
        $expected = 'TEST';
        $actual   = Registry::getItem('test');
        $this->assertEquals($expected, $actual);
    }
}
