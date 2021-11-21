<?php
namespace FileCMSTest\Transform;

use FileCMS\Common\Transform\TransformInterface;
use FileCMS\Transform\RemoveAttributes;
use PHPUnit\Framework\TestCase;
class RemoveAttributesTest extends TestCase
{
    public $clean = NULL;
    public function testImplementsTransformInterface()
    {
        $expected = TRUE;
        $obj = new RemoveAttributes();
        $actual = ($obj instanceof TransformInterface);
        $this->assertEquals($expected, $actual, 'Class does not implement TransformInterface');
    }
    public function testRemovesSingleAttributeArrayParam()
    {
        $str = '<p style="margin-top: 0;">&nbsp;</p>';
        $params = ['attributes' => ['style']];
        $expected = '<p>&nbsp;</p>';
        $obj = new RemoveAttributes();
        $actual = $obj($str, $params);
        $this->assertEquals($expected, $actual, 'Single attribute not removed');
    }
    public function testRemovesSingleAttributeStringParam()
    {
        $str = '<p style="margin-top: 0;">&nbsp;</p>';
        $params = ['attributes' => 'style'];
        $expected = '<p>&nbsp;</p>';
        $obj = new RemoveAttributes();
        $actual = $obj($str, $params);
        $this->assertEquals($expected, $actual, 'Single attribute not removed');
    }
    public function testRemovesMultipleAttributeEvenIfNoValueSet()
    {
        $str = '<p times="" roman="">&nbsp;</p>';
        $params = ['attributes' => 'times,roman'];
        $expected = '<p>&nbsp;</p>';
        $obj = new RemoveAttributes();
        $actual = $obj($str, $params);
        $this->assertEquals($expected, $actual, 'Multiple attributes with no value not removed correctly');
    }
    public function testRemovesSingleNumericAttribute()
    {
        $str = '<td width=300>xxx</td>';
        $params = ['attributes' => ['width']];
        $expected = '<td>xxx</td>';
        $obj = new RemoveAttributes();
        $actual = $obj($str, $params);
        $this->assertEquals($expected, $actual, 'Single numeric attribute not removed properly');
    }
    public function testRemovesSingleUnquotedAttribute()
    {
        $str = '<td valign=top>xxx</td>';
        $params = ['attributes' => ['valign']];
        $expected = '<td>xxx</td>';
        $obj = new RemoveAttributes();
        $actual = $obj($str, $params);
        $this->assertEquals($expected, $actual, 'Single numeric attribute not removed properly');
    }
    public function testRemovesSingleUnquotedAttributeInSelfClosingTag()
    {
        $str = '<input type=text name=test size=40/>';
        $params = ['attributes' => ['size']];
        $expected = '<input type=text name=test />';
        $obj = new RemoveAttributes();
        $actual = $obj($str, $params);
        $this->assertEquals($expected, $actual, 'Single numeric attribute not removed properly');
    }
    public function testRemovesMultipleAttributes()
    {
        $str = '<td width="150" height="20" background="../images/backgrounds/bkgnd_tandk.gif">';
        $params = ['attributes' => ['width', 'height']];
        $expected = '<td background="../images/backgrounds/bkgnd_tandk.gif">';
        $obj = new RemoveAttributes();
        $actual = $obj($str, $params);
        $this->assertEquals($expected, $actual, 'Multiple attributes not removed correctly');
    }
    public function testRemovesMultipleAttributesIfString()
    {
        $str = '<td width="150" height="20" background="../images/backgrounds/bkgnd_tandk.gif">';
        $params = ['attributes' => 'width,height'];
        $expected = '<td background="../images/backgrounds/bkgnd_tandk.gif">';
        $obj = new RemoveAttributes();
        $actual = $obj($str, $params);
        $this->assertEquals($expected, $actual, 'Multiple attributes not removed correctly');
    }
}
