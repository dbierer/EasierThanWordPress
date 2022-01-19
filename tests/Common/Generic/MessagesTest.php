<?php
namespace FileCMSTest\Common\View;

use FileCMS\Common\Generic\Messages;
use PHPUnit\Framework\TestCase;
class MessagesTest extends TestCase
{
    public $messages = NULL;
    public function setUp() : void
    {
        $this->messages = Messages::getInstance();
    }
    public function testConfirmMessageInstanceStartsSession()
    {
        $expected = PHP_SESSION_ACTIVE;
        $actual   = session_status();
        $this->assertEquals($expected, $actual);
    }
    public function testConfirmMessageGetInstanceIsSame()
    {
        $expected = $this->messages;
        $actual   = Messages::getInstance();
        $this->assertEquals($expected, $actual);
    }
    public function testConfirmAddMessage()
    {
        $this->messages->addMessage('TEST');
        $expected = 'TEST';
        $actual   = $this->messages->messages[0];
        // need this to clear messages
        $this->messages->getMessages();
        $this->assertEquals($expected, $actual);
    }
    public function testGetMessagesReturnsEmptyStringIfNoMessages()
    {
        $expected = '';
        $actual   = $this->messages->getMessages();
        $this->assertEquals($expected, $actual);
    }
    public function testGetMessages()
    {
        $this->messages->addMessage('TEST');
        $expected = 'TEST';
        $actual   = $this->messages->getMessages();
        $this->assertEquals($expected, $actual);
    }
    public function testGetMessagesReturnsInReverseOrder()
    {
        $this->messages->addMessage('AAA');
        $this->messages->addMessage('BBB');
        $this->messages->addMessage('CCC');
        $expected = "CCC<br />\nBBB<br />\nAAA";
        $actual   = $this->messages->getMessages();
        $this->assertEquals($expected, $actual);
    }
    public function testMagicDestruct()
    {
        $this->messages->addMessage('TEST');
        $this->messages->__destruct();
        $expected = 'TEST';
        $actual   = unserialize($_SESSION[Messages::MSG_KEY])[0] ?? 'WRONG';
        $this->assertEquals($expected, $actual);
    }
}
