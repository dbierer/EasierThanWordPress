<?php
namespace FileCMSTest\Common\Security;

use Exception;
use FileCMS\Common\Security\Profile;
use PHPUnit\Framework\TestCase;
class ProfileTest extends TestCase
{
    public $config = [];
    public function setUp() : void
    {
        session_reset();
        $this->config = include BASE_DIR . '/tests/config/test.config.php';
    }
    public function testProfileBuildUsesDateAsDefault()
    {
        $expected = ['HTTP_USER_AGENT' => date('Y-m-d')];
        $actual   = Profile::build([]);
        $this->assertEquals($expected, $actual);
    }
    public function testProfileBuildUsesExpectedKeys()
    {
        $date = date('Y-m-d');
        $_SERVER = [];
        $expected = [];
        foreach ($this->config['SUPER']['profile'] as $key) {
            $_SERVER[$key] = $date;
            $expected[$key] = $date;
        }
        Profile::$config = $this->config;
        $actual   = Profile::build();
        $this->assertEquals($expected, $actual);
    }
    public function testProfileBuildUsesExpectedServerAgentIfKeyIsEmpty()
    {
        $key = date('Y-m-d') . 'ABCDEF';
        $_SERVER = ['HTTP_USER_AGENT' => $key];
        $this->config['SUPER']['profile'] = [];
        Profile::$config = $this->config;
        $expected['HTTP_USER_AGENT'] = $key;
        $actual   = Profile::build();
        $this->assertEquals($expected, $actual);
    }
    public function testVerify()
    {
        Profile::init($this->config);
        $expected = TRUE;
        $actual   = Profile::verify();
        $this->assertEquals($expected, $actual);
    }
    public function testVerifyLogsIfFlagSet()
    {
        Profile::init($this->config);
        $err_log = ini_get('error_log');
        file_put_contents($err_log, '');
        Profile::verify(TRUE);
        $contents = file_get_contents($err_log);
        $expected = TRUE;
        $actual   = is_string($contents);
        $this->assertEquals($expected, $actual, 'Contents not a string');
        $expected = TRUE;
        $actual   = strpos($contents, date('Y-m-d') !== FALSE);
        $this->assertEquals($expected, $actual, 'Does not contain expected contents');
    }
    public function testInitCreatesAuthFile()
    {
        Profile::init($this->config);
        $path     = str_replace('//', '/', $this->config['AUTH_DIR'] . '/' . Profile::DEFAULT_AUTH_PREFIX . '*');
        $list     = glob($path);
        $expected = FALSE;
        $actual   = empty($list);
        $this->assertEquals($expected, $actual);
    }
    public function testLogoutWipesOutSession()
    {
        $_SESSION['test'] = 'TEST';
        Profile::logout();
        $expected = TRUE;
        $actual   = empty($_SESSION['test']);
        $this->assertEquals($expected, $actual);
    }
}
