<?php
namespace SimpleHtmlTest\Common\File;

use InvalidArgumentException;
use SimpleHtml\Common\File\Browse;
use PHPUnit\Framework\TestCase;
class BrowseTest extends TestCase
{
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
        $upload = new Browse($config);
    }
    public function testGetThumbFnFromImageFn()
    {
        $this->config['UPLOADS']['upload_dir'] = $this->testFileDir . '/images';
        $this->config['UPLOADS']['thumb_dir']  = $this->testFileDir . '/thumb';
        $browse = new Browse($this->config);
        $img_fn = $this->testFileDir . '/images/blog-1.jpg';
        $expected = $this->testFileDir . '/thumb/blog-1.jpg';
        $actual = $browse->getThumbFnFromImageFn($img_fn);
        $this->assertEquals($expected, $actual, 'Thumb image FN not created correctly');
    }
    public function testGetThumbUrlFromImageUrl()
    {
        $browse = new Browse($this->config);
        $img_url = '/images/blog-1.jpg';
        $expected = '/thumb/blog-1.jpg';
        $actual = $browse->getThumbUrlFromImageUrl($img_url);
        $this->assertEquals($expected, $actual, 'Thumb image URL not created correctly');
    }
    public function testGetListOfImagesReturnsCorrectCount()
    {
        $img_dir = $this->testFileDir . '/images';
        $this->config['UPLOADS']['upload_dir'] = $img_dir;
        $browse = new Browse($this->config);
        $expected = count(glob($img_dir . '/*'));
        $images = $browse->getListOfImages($img_dir);
        $actual = count($images);
        $this->assertEquals($expected, $actual, 'Browse::getListOfImages() did not return correct count');
    }
}
