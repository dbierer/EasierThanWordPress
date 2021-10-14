<?php
namespace SimpleHtmlTest\Transform;

use SimpleHtml\Transform\{InsertBefore,TransformInterface};
use PHPUnit\Framework\TestCase;
class InsertBeforeTest extends TestCase
{
    public $insert = NULL;
    public function testImplementsTransformInterface()
    {
        $expected = TRUE;
        $insert = new InsertBefore();
        $actual = ($insert instanceof TransformInterface);
        $this->assertEquals($expected, $actual, 'Class does not implement TransformInterface');
    }
    public function testInvokeInsertsText()
    {
        $html = '<p>Test</p>';
        $params  = ['text' => '<h1>Test</h1>'];
        $insert = new InsertBefore();
        $expected = '<h1>Test</h1><p>Test</p>';
        $actual = (new InsertBefore())($html, $params);
        $this->assertEquals($expected, $actual, 'Text was not inserted.');
    }
    public function testInvokeTakesNoActionIfTextParamMissing()
    {
        $html = '<p>Test</p>';
        $params  = [];
        $insert = new InsertBefore();
        $expected = $html;
        $actual = (new InsertBefore())($html, $params);
        $this->assertEquals($expected, $actual, 'Original HTML not returned if "text" param missing.');
    }
}
