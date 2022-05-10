<?php
namespace FileCMSTest\Common\Contact;

use FileCMS\Common\Contact\Email;
use PHPUnit\Framework\TestCase;
class EmailTest extends TestCase
{
    public function setUp() : void
    {
        // do something
    }
    public function testValidateEmail()
    {
        $expected = 1;
        $actual   = 0;
        $this->assertEquals($expected, $actual);
    }
    public function testQueueOutbound()
    {
        $expected = 1;
        $actual   = 0;
        $this->assertEquals($expected, $actual);
    }
    public function testProcessPost()
    {
        $expected = 1;
        $actual   = 0;
        $this->assertEquals($expected, $actual);
    }
    public function testConfirmAndSend()
    {
        $expected = 1;
        $actual   = 0;
        $this->assertEquals($expected, $actual);
    }
    public function testTrustedSend()
    {
        $expected = 1;
        $actual   = 0;
        $this->assertEquals($expected, $actual);
    }
}
