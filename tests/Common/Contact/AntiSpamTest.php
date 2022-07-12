<?php
namespace FileCMSTest\Common\Contact;

use PHPUnit\Framework\TestCase;
use FileCMS\Common\Contact\AntiSpam;
use FileCMS\Common\Generic\Messages;
class AntiSpamTest extends TestCase
{
    public $config = [];
    public $mail   = NULL;
    public function setUp() : void
    {
        $this->config = include BASE_DIR . '/tests/config/test.config.php';
    }
    public function testVerifyCaptchaReturnsTrue()
    {
        $hash = password_hash('password', PASSWORD_DEFAULT);
        $_SESSION[$this->config['CAPTCHA']['sess_hash_key']] = $hash;
        $expected = TRUE;
        $actual   = AntiSpam::verifyCaptcha($this->config, 'password');
        $this->assertEquals($expected, $actual);
    }
    public function testVerifyCaptchaReturnsFalse()
    {
        $hash = password_hash('password', PASSWORD_DEFAULT);
        $_SESSION[$this->config['CAPTCHA']['sess_hash_key']] = $hash;
        $expected = FALSE;
        $actual   = AntiSpam::verifyCaptcha($this->config, 'password');
        $this->assertEquals($expected, $actual);
    }
    public function testVerifyCaptchaAddsMessage()
    {
        $message = Messages::getInstance();
        $message->getMessages();
        $hash = password_hash('password', PASSWORD_DEFAULT);
        $_SESSION[$this->config['CAPTCHA']['sess_hash_key']] = $hash;
        AntiSpam::verifyCaptcha($this->config, 'bad password');
        $expected = AntiSpam::ERR_UNABLE;
        $actual   = $message->getMessages();
        $this->assertEquals($expected, $actual);
    }
}
