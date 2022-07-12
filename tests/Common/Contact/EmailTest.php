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
        $msg      = '';
        $email    = 'bad@email@bad.bad';
        $expected = FALSE;
        $actual   = Email::validateEmail($email, $msg);
        $this->assertEquals($expected, $actual);
    }
    public function testValidateEmailReturnsTrueIfGoodEmailNoMxCheck()
    {
        $msg      = '';
        $email    = 'doug@unlikelysource.com';
        $expected = TRUE;
        $actual   = Email::validateEmail($email, $msg, FALSE);
        $this->assertEquals($expected, $actual);
    }
    public function testValidateEmailReturnsFalseIfBadMxRecord()
    {
        $msg      = '';
        $email    = 'bad@unlikelysource.biz';
        $expected = FALSE;
        $actual   = Email::validateEmail($email, $msg, TRUE);
        $this->assertEquals($expected, $actual);
    }
    public function testValidateEmailReturnsTrueIfMxRecordExists()
    {
        $msg      = '';
        $email    = 'doug@unlikelysource.com';
        $expected = TRUE;
        $actual   = Email::validateEmail($email, $msg, TRUE);
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
    public function testProcessPostReturnsExpectedErrorIfEmailInputMissing()
    {
        $inputs   = ['email' => ''];
        $body     = 'TEST ' . date('Y-m-d H:i:s');
        $mx_check = FALSE;
        $debug    = TRUE;
        $msg      = Email::processPost($this->config, $inputs, $body, $mx_check, $debug);
        $expected = $this->config['COMPANY_EMAIL']['ERROR'];
        $actual   = $msg;
        $this->assertEquals($expected, $actual);
    }
    public function testProcessPostReturnsExpectedErrorIfEmailInvalid()
    {
        $inputs   = ['email' => 'bad@bad@email'];
        $body     = 'TEST ' . date('Y-m-d H:i:s');
        $mx_check = FALSE;
        $debug    = TRUE;
        $msg      = Email::processPost($this->config, $inputs, $body, $mx_check, $debug);
        $expected = $this->config['COMPANY_EMAIL']['ERROR'];
        $actual   = $msg;
        $this->assertEquals($expected, $actual);
    }
    public function testProcessPostReturnsExpectedErrorIfEmailInvalidMx()
    {
        $inputs   = ['email' => 'doug@unlikelysource.biz'];
        $body     = 'TEST ' . date('Y-m-d H:i:s');
        $mx_check = TRUE;
        $debug    = TRUE;
        $msg      = Email::processPost($this->config, $inputs, $body, $mx_check, $debug);
        $expected = Email::ERR_MX;
        $actual   = $msg;
        $this->assertEquals($expected, $actual);
    }
    public function testProcessPostReturnsExpectedBody()
    {
        Email::$phpMailer->Body = '';
        $inputs   = ['email' => 'doug@unlikelysource.com', 'one' => 111, 'two' => 222];
        $body     = '';
        $mx_check = FALSE;
        $debug    = TRUE;
        $msg      = Email::processPost($this->config, $inputs, $body, $mx_check, $debug);
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
        Email::$phpMailer->Body = '';
        $inputs   = ['email' => 'doug@unlikelysource.com', 'one' => 111, 'two' => 222];
        $body     = 'TEST ' . date('Y-m-d H:i:s');
        $mx_check = FALSE;
        $debug    = TRUE;
        $msg      = Email::processPost($this->config, $inputs, $body, $mx_check, $debug);
        $expected = $body;
        $actual   = Email::$phpMailer->Body;
        $this->assertEquals($expected, $actual);
    }
}
