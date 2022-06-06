<?php
namespace FileCMSTest\Common\Contact;

use FileCMS\Common\Contact\Email;
use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;
class EmailTest extends TestCase
{
    public $config = [];
    public $mail   = NULL;
    public function setUp() : void
    {
        $this->config = include BASE_DIR . '/tests/config/test.config.php';
        $this->mail = new class() extends PHPMailer {
            public function getTo() { return $this->to; }
            public function getCc() { return $this->cc; }
            public function getBcc() { return $this->bcc; }
            public function getMIMEBody() { return $this->MIMEBody; }
        };
    }
    public function testValidateEmailReturnsFalseIfBadEmail()
    {
        $email    = 'bad@email@bad.bad';
        $expected = FALSE;
        $actual   = Email::validateEmail($email);
        $this->assertEquals($expected, $actual);
    }
    public function testValidateEmailReturnsTrueIfGoodEmail()
    {
        $email    = 'doug@unlikelysource.com';
        $expected = TRUE;
        $actual   = Email::validateEmail($email);
        $this->assertEquals($expected, $actual);
    }
    public function testQueueOutboundAddsToCcBcc()
    {
        $data = [
            'to'  => 'doug@unlikelysource.com',
            'cc'  => ['test1@unlikelysource.com','test2@unlikelysource.com'],
            'bcc' => 'info@unlikelysource.com',
        ];
        foreach ($data as $key => $value)
            Email::queueOutbound($value, $key, $this->mail);
        $expected = $data['to'];
        $actual   = $this->mail->getTo()[0][0];
        $this->assertEquals($expected, $actual, 'To does not match');
        $expected = [ 0 => [ 0 => $data['cc'][0], 1 => ''],
                      1 => [ 0 => $data['cc'][1], 1 => '']
        ];
        $actual   = $this->mail->getCc();
        $this->assertEquals($expected, $actual, 'CC does not match');
        $expected = $data['bcc'];
        $actual   = $this->mail->getBcc()[0][0];
        $this->assertEquals($expected, $actual, 'BCC does not match');
    }
    public function testTrustedSendReturnsFailMsgOnInvalidSend()
    {
        $to = 'doug@unlikelysource.com';
        $from = 'test@unlikelysource.com';
        $subject = 'TEST ' . date('Y-m-d H:i:s');
        $body = $subject;
        $cc = '';
        $bcc = '';
        $debug = FALSE;
        $msg = Email::trustedSend($this->config, $to, $from, $subject, $body, $cc, $bcc, $debug);
        $expected = $this->config['COMPANY_EMAIL']['ERROR'];
        $actual   = $msg;
        $this->assertEquals($expected, $actual);
    }
    public function testTrustedSendDoesNotUseSMTP()
    {
        $to = 'doug@unlikelysource.com';
        $from = 'test@unlikelysource.com';
        $subject = 'TEST ' . date('Y-m-d H:i:s');
        $body = $subject;
        $cc = '';
        $bcc = '';
        $debug = TRUE;
        $this->config['COMPANY_EMAIL']['phpmailer']['smtp'] = FALSE;
        $msg = Email::trustedSend($this->config, $to, $from, $subject, $body, $cc, $bcc, $debug);
        $expected = 'mail';
        $actual   = get_object_vars(Email::$phpMailer)['Mailer'];
        $this->assertEquals($expected, $actual);
    }
    public function testTrustedSendIsHtmlIfConfigSet()
    {
        $to = 'doug@unlikelysource.com';
        $from = 'test@unlikelysource.com';
        $subject = 'TEST ' . date('Y-m-d H:i:s');
        $body = '<p>' . $subject . '</p>';
        $cc = '';
        $bcc = '';
        $debug = TRUE;
        $this->config['COMPANY_EMAIL']['phpmailer']['html'] = TRUE;
        $msg = Email::trustedSend($this->config, $to, $from, $subject, $body, $cc, $bcc, $debug);
        $expected = 'text/html';
        $actual   = get_object_vars(Email::$phpMailer)['ContentType'];
        $this->assertEquals($expected, $actual);
    }
    public function testTrustedSendIsNotHtmlIfConfigSetFalse()
    {
        $to = 'doug@unlikelysource.com';
        $from = 'test@unlikelysource.com';
        $subject = 'TEST ' . date('Y-m-d H:i:s');
        $body = '<p>' . $subject . '</p>';
        $cc = '';
        $bcc = '';
        $debug = TRUE;
        $this->config['COMPANY_EMAIL']['phpmailer']['html'] = FALSE;
        $msg = Email::trustedSend($this->config, $to, $from, $subject, $body, $cc, $bcc, $debug);
        $expected = 'text/plain';
        $actual   = get_object_vars(Email::$phpMailer)['ContentType'];
        $this->assertEquals($expected, $actual);
    }
    public function testTrustedSendUsesSMTPIfConfigSet()
    {
        $to = 'doug@unlikelysource.com';
        $from = 'test@unlikelysource.com';
        $subject = 'TEST ' . date('Y-m-d H:i:s');
        $body = $subject;
        $cc = '';
        $bcc = '';
        $debug = TRUE;
        $this->config['COMPANY_EMAIL']['phpmailer']['smtp'] = TRUE;
        $msg = Email::trustedSend($this->config, $to, $from, $subject, $body, $cc, $bcc, $debug);
        $expected = 'smtp';
        $actual   = get_object_vars(Email::$phpMailer)['Mailer'];
        $this->assertEquals($expected, $actual);
    }
    public function testConfirmAndSendFailsOnInvalidPassword()
    {
        $from = 'test@unlikelysource.com';
        $subject = 'TEST ' . date('Y-m-d H:i:s');
        $body = $subject;
        $debug = FALSE;
        $hash = password_hash('password', PASSWORD_DEFAULT);
        $_REQUEST[$this->config['CAPTCHA']['input_tag_name']] = 'bad password';
        $_SESSION[$this->config['CAPTCHA']['sess_hash_key']] = $hash;
        $msg = Email::confirmAndSend($from, $this->config, $subject, $body, $debug);
        $expected = $this->config['COMPANY_EMAIL']['ERROR'];
        $actual   = $msg;
        $this->assertEquals($expected, $actual);
    }
    public function testConfirmAndSendFailsOnInvalidFrom()
    {
        $from = 'bad@bad@bad';
        $subject = 'TEST ' . date('Y-m-d H:i:s');
        $body = $subject;
        $debug = FALSE;
        $hash = password_hash('password', PASSWORD_DEFAULT);
        $_REQUEST[$this->config['CAPTCHA']['input_tag_name']] = 'password';
        $_SESSION[$this->config['CAPTCHA']['sess_hash_key']] = $hash;
        $msg = Email::confirmAndSend($from, $this->config, $subject, $body, $debug);
        $expected = $this->config['COMPANY_EMAIL']['ERROR'];
        $actual   = $msg;
        $this->assertEquals($expected, $actual);
    }
    public function testProcessPostReturnsExpectedErrorIfEmailInputMissing()
    {
        $inputs = ['email' => ''];
        $body = 'TEST ' . date('Y-m-d H:i:s');
        $debug = FALSE;
        $hash = password_hash('password', PASSWORD_DEFAULT);
        $_REQUEST[$this->config['CAPTCHA']['input_tag_name']] = 'password';
        $_SESSION[$this->config['CAPTCHA']['sess_hash_key']] = $hash;
        $msg = Email::processPost($this->config, $inputs, $body, $debug);
        $expected = $this->config['COMPANY_EMAIL']['ERROR'];
        $actual   = $msg;
        $this->assertEquals($expected, $actual);
    }
    public function testProcessPostReturnsExpectedBody()
    {
        $inputs = ['email' => 'doug@unlikelysource.com', 'one' => 111, 'two' => 222];
        $body = '';
        $debug = FALSE;
        $hash = password_hash('password', PASSWORD_DEFAULT);
        $_REQUEST[$this->config['CAPTCHA']['input_tag_name']] = 'password';
        $_SESSION[$this->config['CAPTCHA']['sess_hash_key']] = $hash;
        $msg = Email::processPost($this->config, $inputs, $body, $debug);
        $expected = <<<EOT

Email                : doug@unlikelysource.com
One                  : 111
Two                  : 222

EOT;
        $actual   = $body;
        $this->assertEquals($expected, $actual);
    }
    public function testProcessPostReturnsExpectedBodyInMailInstance()
    {
        $inputs = ['email' => 'doug@unlikelysource.com', 'one' => 111, 'two' => 222];
        $body = '';
        $debug = TRUE;
        $hash = password_hash('password', PASSWORD_DEFAULT);
        $_REQUEST[$this->config['CAPTCHA']['input_tag_name']] = 'password';
        $_SESSION[$this->config['CAPTCHA']['sess_hash_key']] = $hash;
        $msg = Email::processPost($this->config, $inputs, $body, $debug);
        $text = <<<EOT
'Body' => '
 Email                : doug@unlikelysource.com
 One                  : 111
 Two                  : 222
'

EOT;
        $export   = var_export(Email::$phpMailer, TRUE);
        $expected = 3;
        $actual   = 0;
        $actual  += (bool) strpos($export, $inputs['email']);
        $actual  += (bool) strpos($export, $inputs['one']);
        $actual  += (bool) strpos($export, $inputs['two']);
        $this->assertEquals($expected, $actual);
    }
}
