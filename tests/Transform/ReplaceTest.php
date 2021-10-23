<?php
namespace SimpleHtmlTest\Transform;

use SimpleHtml\Transform\{Replace,TransformInterface};
use PHPUnit\Framework\TestCase;
class ReplaceTest extends TestCase
{
    public $replace = NULL;
    public function testImplementsTransformInterface()
    {
        $expected = TRUE;
        $replace = new Replace();
        $actual = ($replace instanceof TransformInterface);
        $this->assertEquals($expected, $actual, 'Class does not implement TransformInterface');
    }
    public function testInvokeRemovesText()
    {
        $text = '<img src="https://my.web.site.com/images/test" />';
        $expected = '<img src="/images/test" />';
        $params = ['search' => 'https://my.web.site.com', 'replace' => '', 'case-sensitive' => FALSE];
        $actual = (new Replace())($text, $params);
        $this->assertEquals($expected, $actual, 'Text was not replaced.');
    }
    public function testInvokeCaseInSensitiveWorks()
    {
        $text = '<img src="https://my.web.site.com/images/test" />';
        $expected = '<img src="/images/test" />';
        $params = ['search' => 'HTTPS://my.web.site.com', 'replace' => '', 'case-sensitive' => FALSE];
        $actual = (new Replace())($text, $params);
        $this->assertEquals($expected, $actual, 'Replacement was not case-insensitive');
    }
    public function testInvokeCaseSensitiveWorks()
    {
        $text = '<img src="https://my.web.site.com/images/test" />';
        $expected = '<img src="https://my.web.site.com/images/test" />';
        $params = ['search' => 'HTTPS://my.web.site.com', 'replace' => '', 'case-sensitive' => TRUE];
        $actual = (new Replace())($text, $params);
        $this->assertEquals($expected, $actual, 'Replacement was not case-sensitive');
    }
    public function testInvokeReplacesMultiple()
    {
        $text = '<img src="https://my.web.site.com/images/test1.jpg" /><img src="images/test2.jpg" />';
        $expected = '<img src="/images/test1.jpg" /><img src="/images/test2.jpg" />';
        $params = ['search' => ['https://my.web.site.com','src="images'], 'replace' => ['','src="/images'], 'case-sensitive' => FALSE];
        $actual = (new Replace())($text, $params);
        $this->assertEquals($expected, $actual, 'Text was not replaced.');
    }
}
