<?php
namespace EasierThanWordPressTest\Transform;

use EasierThanWordPress\Transform\{RemoveAttributes,TransformInterface};
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
    public function testRemovesSingleAttribute()
    {
        $str = '<p style="margin-top: 0;">&nbsp;</p>';
        $params = ['attributes' => ['style']];
        $expected = '<p>&nbsp;</p>';
        $obj = new RemoveAttributes();
        $actual = $obj($str, $params);
        $this->assertEquals($expected, $actual, 'Single attribute not removed');
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
    public function testRemovesMultipleAttributes()
    {
        $str = '<td width="150" height="20" background="../images/backgrounds/bkgnd_tandk.gif">';
        $params = ['attributes' => ['width', 'height']];
        $expected = '<td background="../images/backgrounds/bkgnd_tandk.gif">';
        $obj = new RemoveAttributes();
        $actual = $obj($str, $params);
        $this->assertEquals($expected, $actual, 'Multiple attributes not removed correctly');
    }
}
