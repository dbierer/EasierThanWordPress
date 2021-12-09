<?php
namespace FileCMSTest\Common\Security;

use Exception;
use FileCMS\Common\Security\Filter;
use FileCMS\Common\Security\Validation;
use PHPUnit\Framework\TestCase;
class BaseTest extends TestCase
{
    public function testThrowsExceptionIfUndefinedValidationMethodCalled()
    {
        $this->expectException(Exception::class);
        $callbacks = [
            'does_not_exist' => [],
        ];
        $text     = 'TEST';
        $expected = 'TEST';
        $actual   = Validation::runValidators($text, $callbacks);
        $this->assertEquals($expected, $actual);
    }
    public function testThrowsExceptionIfUndefinedFilterMethodCalled()
    {
        $this->expectException(Exception::class);
        $callbacks = [
            'does_not_exist' => [],
        ];
        $text     = 'TEST';
        $expected = 'TEST';
        $actual   = Filter::runFilters($text, $callbacks);
        $this->assertEquals($expected, $actual);
    }
}
