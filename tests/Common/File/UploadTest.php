<?php
namespace SimpleHtmlTest\Common\File;

use InvalidArgumentException;
use SimpleHtml\Common\File\Upload;
use PHPUnit\Framework\TestCase;
class UploadTest extends TestCase
{
    public $upload;
    public $testFileDir = '';
    public $config = [];
    public function setUp() : void
    {
        $this->testFileDir = realpath(__DIR__ . '/../../test_files');
        $this->config = include __DIR__ . '/../../../src/config/config.php';
    }
    public function testUploadThrowsInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $config = [];
        $upload = new Upload($config);
    }
    public function testUploadReturnsErrorResponseIfNotUploadedFile()
    {
        $upload = new Upload($this->config);
        $expected = 0;
        $response = $upload->handle('upload');
        $actual   = $response['uploaded'];
        $this->assertEquals($expected, $actual, 'Did not return correct error response');
    }
    public function testUploadReturnsErrorResponseIfMissingExtension()
    {
        $upload = new Upload($this->config);
        $expected = 'ERROR: invalid file type';
        $_FILES['upload'] = [
            'name' => 'missing_ext',
            'tmp_name' => '/tmp/xxx'
        ];
        $response = $upload->handle('upload');
        $actual   = substr($response['error'], 0, strlen($expected));
        $this->assertEquals($expected, $actual, 'Did not return correct error response if missing extension');
    }
    public function testUploadReturnsErrorResponseIfInvalidExtension()
    {
        $upload = new Upload($this->config);
        $expected = 'ERROR: invalid file type: php';
        $_FILES['upload'] = [
            'name' => 'invalid_ext.php',
            'tmp_name' => '/tmp/xxx'
        ];
        $response = $upload->handle('upload');
        $actual   = $response['error'];
        $this->assertEquals($expected, $actual, 'Did not return correct error response if invalid extension');
    }
    public function testUploadReturnsErrorResponseIfInvalidWidth()
    {
        $upload = new Upload($this->config);
        $upload->config['restrict_size'] = TRUE;
        $upload->config['img_width'] = 1;
        $expected = 'ERROR: existing width x height';
        $response = $upload->checkImageSize($this->testFileDir . '/fon.png');
        $error    = $upload->errors[0] ?? '';
        $actual   = substr($error, 0, strlen($expected));
        $this->assertEquals($expected, $actual, 'Did not return correct error response if invalid width');
    }
    public function testUploadReturnsErrorResponseIfInvalidHeight()
    {
        $upload = new Upload($this->config);
        $upload->config['restrict_size'] = TRUE;
        $upload->config['img_height'] = 1;
        $expected = 'ERROR: existing width x height';
        $response = $upload->checkImageSize($this->testFileDir . '/fon.png');
        $error    = $upload->errors[0] ?? '';
        $actual   = substr($error, 0, strlen($expected));
        $this->assertEquals($expected, $actual, 'Did not return correct error response if invalid height');
    }
    public function testUploadReturnsErrorResponseIfInvalidSize()
    {
        $upload = new Upload($this->config);
        $upload->config['restrict_size'] = TRUE;
        $upload->config['img_size'] = 1;
        $expected = 'ERROR: maximum file size';
        $response = $upload->checkImageSize($this->testFileDir . '/fon.png');
        $error    = $upload->errors[0] ?? '';
        $actual   = substr($error, 0, strlen($expected));
        $this->assertEquals(FALSE, $response, 'Did not return FALSE error response if invalid size');
        $this->assertEquals($expected, $actual, 'Did not return correct error response if invalid size');
    }
    public function testUploadReturnsErrorResponseIfInvalidType()
    {
        $upload = new Upload($this->config);
        $upload->config['restrict_size'] = TRUE;
        $expected = 'ERROR: invalid file type: text';
        $response = $upload->checkImageSize($this->testFileDir . '/fake.png');
        $error    = $upload->errors[0] ?? '';
        $actual   = substr($error, 0, strlen($expected));
        $this->assertEquals($expected, $actual, 'Did not return correct error response if invalid MIME type');
    }
}
