<?php
namespace FileCMSTest\Common\Security;

use Exception;
use FileCMS\Common\Security\Profile;
use PHPUnit\Framework\TestCase;
class ProfileTest extends TestCase
{
    public function testProfileBuild()
    {
        $expected = 1;
        $actual   = 0;
        $this->assertEquals($expected, $actual);
    }
    public function testProfileVerifySuccessDefinesSessionOptions()
    {
        $expected = 1;
        $actual   = 0;
        $this->assertEquals($expected, $actual);
    }
}
