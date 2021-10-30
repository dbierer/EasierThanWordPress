<?php
namespace EasierThanWordPressTest\Transform;

use EasierThanWordPress\Transform\{ReplaceRegex,TransformInterface};
use PHPUnit\Framework\TestCase;
class ReplaceRegexTest extends TestCase
{
    public $replace = NULL;
    public function testImplementsTransformInterface()
    {
        $expected = TRUE;
        $replace = new ReplaceRegex();
        $actual = ($replace instanceof TransformInterface);
        $this->assertEquals($expected, $actual, 'Class does not implement TransformInterface');
    }
    public function testInvokeReplacesText()
    {
        $text = '<p><a href="https://unlikelysource.com/something/else.html">blah blah blah</a></p>';
        $expected = '<p><a href="/something/else">blah blah blah</a></p>';
        $params = ['regex' => '!https://unlikelysource.com(.*?).html!', 'replace' => '$1'];
        $actual = (new ReplaceRegex())($text, $params);
        $this->assertEquals($expected, $actual, 'Text was not replaced.');
    }
    public function testInvokeArrayOfRegexesReplacesText()
    {
        $text = '<p><a href="http://test.com/something/else.html">xxx</a><a href="http://www.test.com/something/else/again.html">yyy</a></p>';
        $expected = '<p><a href="/something/else">xxx</a><a href="/something/else/again">yyy</a></p>';
        $params = ['regex' => ['!http://test.com(.*?).html!','!http://www.test.com(.*?).html!'], 'replace' => '$1'];
        $actual = (new ReplaceRegex())($text, $params);
        $this->assertEquals($expected, $actual, 'Text was not replaced from array of regexes.');
    }
}
