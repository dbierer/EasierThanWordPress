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
        $actual   = Profile::build($this->config);
        $this->assertEquals($expected, $actual);
    }
    public function testProfileBuildUsesExpectedServerAgentIfKeyIsEmpty()
    {
        $key = date('Y-m-d') . 'ABCDEF';
        $_SERVER = ['HTTP_USER_AGENT' => $key];
        $this->config['SUPER']['profile'] = [];
        $expected['HTTP_USER_AGENT'] = $key;
        $actual   = Profile::build($this->config);
        $this->assertEquals($expected, $actual);
    }
    public function testVerify()
    {
        Profile::init($this->config);
        $expected = TRUE;
        $actual   = Profile::verify($this->config);
        $this->assertEquals($expected, $actual);
    }
    public function testInitCreatesAuthFile()
    {
        Profile::init($this->config);
        $path     = str_replace('//', '/', $this->config['AUTH_DIR'] . '/' . Profile::DEFAULT_AUTH_PREFIX . '*');
        $list     = glob($path);
        $expected = TRUE;
        $actual   = empty($list);
        $this->assertEquals($expected, $actual);
    }
    public function testLogoutWipesOutSession()
    {
        $_SESSION['test'] = 'TEST';
        Profile::logout($this->config);
        $expected = TRUE;
        $actual   = empty($_SESSION['test']);
        $this->assertEquals($expected, $actual);
    }
}
