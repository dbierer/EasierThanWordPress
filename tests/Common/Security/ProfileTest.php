<?php
namespace FileCMSTest\Common\Security;

use Exception;
use FileCMS\Common\Security\Profile;
use PHPUnit\Framework\TestCase;
class ProfileTest extends TestCase
{
    public function testProfileBuildUsesDateAsDefault()
    {
        $expected = md5(date('Y-m-d'));
        $actual   = Profile::build([]);
        $this->assertEquals($expected, $actual);
    }
    public function testProfileBuildUsesExpectedKeys()
    {
        $config = include BASE_DIR . '/tests/config/test.config.php';
        foreach ($config['SUPER']['profile'] as $key) {
            $_SERVER[$key] = date('Y-m-d');
        }
        $profile = [
            date('Y-m-d'),
            date('Y-m-d'),
            date('Y-m-d')
        ];
        $expected = md5(implode('|', $profile));
        $actual   = Profile::build($config);
        $this->assertEquals($expected, $actual);
    }
    public function testProfileBuildUsesExpectedServerAgentIfKeyIsEmpty()
    {
        $_SERVER['HTTP_USER_AGENT'] = date('Y-m-d') . 'ABCDEF';
        $config = include BASE_DIR . '/tests/config/test.config.php';
        $config['SUPER']['profile'] = [];
        $expected = md5($_SERVER['HTTP_USER_AGENT']);
        $actual   = Profile::build($config);
        $this->assertEquals($expected, $actual);
    }
    public function testSetStoresProfileInSession()
    {
        $hash = md5(date('Y-m-d'));
        Profile::set($hash);
        $expected = $hash;
        $actual   = $_SESSION[Profile::PROFILE_KEY] ?? 'NOT';
        $this->assertEquals($expected, $actual);
    }
    public function testGetReturnsCorrectValueFromSession()
    {
        $hash = md5(date('Y-m-d'));
        Profile::set($hash);
        $expected = $hash;
        $actual   = Profile::get();
        $this->assertEquals($expected, $actual);
    }
    public function testVerify()
    {
        $config = include BASE_DIR . '/tests/config/test.config.php';
        foreach ($config['SUPER']['profile'] as $key) {
            $_SERVER[$key] = date('Y-m-d');
        }
        $profile = [
            date('Y-m-d'),
            date('Y-m-d'),
            date('Y-m-d')
        ];
        $hash = md5(implode('|', $profile));
        Profile::set($hash);
        Profile::build($config);
        $expected = TRUE;
        $actual   = Profile::verify($config);
        $this->assertEquals($expected, $actual);
    }
}
