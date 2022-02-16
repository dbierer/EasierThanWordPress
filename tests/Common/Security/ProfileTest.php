<?php
namespace FileCMSTest\Common\Security;

use Exception;
use FileCMS\Common\Security\Profile;
use PHPUnit\Framework\TestCase;
class ProfileTest extends TestCase
{
    public function setUp() : void
    {
        session_reset();
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
        $config = include BASE_DIR . '/tests/config/test.config.php';
        foreach ($config['SUPER']['profile'] as $key) {
            $_SERVER[$key] = $date;
            $expected[$key] = $date;
        }
        $actual   = Profile::build($config);
        $this->assertEquals($expected, $actual);
    }
    public function testProfileBuildUsesExpectedServerAgentIfKeyIsEmpty()
    {
        $key = date('Y-m-d') . 'ABCDEF';
        $_SERVER = ['HTTP_USER_AGENT' => $key];
        $config = include BASE_DIR . '/tests/config/test.config.php';
        $config['SUPER']['profile'] = [];
        $expected['HTTP_USER_AGENT'] = $key;
        $actual   = Profile::build($config);
        $this->assertEquals($expected, $actual);
    }
    public function testSetStoresProfileInSession()
    {
        $test = ['test' => 'TEST'];
        Profile::set($test);
        $expected = $test;
        $actual   = $_SESSION[Profile::PROFILE_KEY] ?? 'NOT';
        $this->assertEquals($expected, $actual);
    }
    public function testGetReturnsCorrectValueFromSession()
    {
        $test = ['test' => 'TEST'];
        Profile::set($test);
        $expected = $test;
        $actual   = Profile::get();
        $this->assertEquals($expected, $actual);
    }
    public function testVerify()
    {
        $config = include BASE_DIR . '/tests/config/test.config.php';
        Profile::init($config);
        $expected = TRUE;
        $actual   = Profile::verify($config);
        $this->assertEquals($expected, $actual);
    }
    public function testInit()
    {
        $config = include BASE_DIR . '/tests/config/test.config.php';
        Profile::init($config);
        $expected = Profile::build($config);
        $actual   = Profile::get($config);
        $this->assertEquals($expected, $actual);
    }
}
