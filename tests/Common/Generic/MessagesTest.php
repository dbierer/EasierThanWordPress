<?php
namespace FileCMSTest\Common\View;

use FileCMS\Common\Generic\Messages;
use PHPUnit\Framework\TestCase;
class MessagesTest extends TestCase
{
    public function testConfirmMessageInstanceStartsSession()
    {
        $expected = 'TEST';
        $actual   = 'NOT';
        $this->assertEquals($expected, $actual);
    }
}
