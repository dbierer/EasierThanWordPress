<?php
namespace FileCMSTest\Transform;

use FileCMS\Transform\{Prepend,TransformInterface};
use PHPUnit\Framework\TestCase;
class PrependTest extends TestCase
{
    public $insert = NULL;
    public function testImplementsTransformInterface()
    {
        $expected = TRUE;
        $insert = new Prepend();
        $actual = ($insert instanceof TransformInterface);
        $this->assertEquals($expected, $actual, 'Class does not implement TransformInterface');
    }
    public function testInvokeInsertsText()
    {
        $html = '<p>Test</p>';
        $params  = ['text' => '<h1>Test</h1>'];
        $insert = new Prepend();
        $expected = '<h1>Test</h1><p>Test</p>';
        $actual = (new Prepend())($html, $params);
        $this->assertEquals($expected, $actual, 'Text was not inserted.');
    }
    public function testInvokeTakesNoActionIfTextParamMissing()
    {
        $html = '<p>Test</p>';
        $params  = [];
        $insert = new Prepend();
        $expected = $html;
        $actual = (new Prepend())($html, $params);
        $this->assertEquals($expected, $actual, 'Original HTML not returned if "text" param missing.');
    }
}
