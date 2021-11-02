<?php
namespace FileCMSTest\Transform;

use FileCMS\Common\Transform\TransformInterface;
use FileCMS\Transform\Append;
use PHPUnit\Framework\TestCase;
class AppendTest extends TestCase
{
    public $insert = NULL;
    public function testImplementsTransformInterface()
    {
        $expected = TRUE;
        $insert = new Append();
        $actual = ($insert instanceof TransformInterface);
        $this->assertEquals($expected, $actual, 'Class does not implement TransformInterface');
    }
    public function testInvokeInsertsText()
    {
        $html = '<h1>Test</h1>';
        $params  = ['text' => '<p>Test</p>'];
        $expected = '<h1>Test</h1><p>Test</p>';
        $actual = (new Append())($html, $params);
        $this->assertEquals($expected, $actual, 'Text was not inserted.');
    }
    public function testInvokeTakesNoActionIfTextParamMissing()
    {
        $html = '<p>Test</p>';
        $params  = [];
        $expected = $html;
        $actual = (new Append())($html, $params);
        $this->assertEquals($expected, $actual, 'Original HTML not returned if "text" param missing.');
    }
}
