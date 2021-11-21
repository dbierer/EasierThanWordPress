<?php
namespace FileCMSTest\Common\File;

use FilterIterator;
use InvalidArgumentException;
use FileCMS\Common\File\Browse;
use PHPUnit\Framework\TestCase;
class BrowseTest extends TestCase
{
    public $testFileDir = '';
    public $config = [];
    public function setUp() : void
    {
        $this->testFileDir = realpath(__DIR__ . '/../../test_files');
        $this->testImgDir = $this->testFileDir . '/images';
        $this->config = include __DIR__ . '/../../../src/config/config.php';
        $this->config['UPLOADS']['img_dir'] = $this->testFileDir . '/images';
        $this->config['UPLOADS']['thumb_dir']  = $this->testFileDir . '/thumb';
    }
    public function testUploadThrowsInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $config = [];
        $upload = new Browse($config);
    }
    public function testGetThumbFnFromImageFn()
    {
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
        $img_fn  = $this->testFileDir . $img_url;
        $expected = '/thumb/blog-1.jpg';
        $actual = $browse->getThumbUrlFromImageUrl($img_url, $img_fn);
        $this->assertEquals($expected, $actual, 'Thumb image URL not created correctly');
    }
    public function testGetThumbUrlReturnsImageUrlIfThumbFileNotFound()
    {
        $browse = new Browse($this->config);
        $img_fn = $this->testFileDir . '/images/blog-2.jpg';
        $img_url = '/images/blog-2.jpg';
        $expected = '/images/blog-2.jpg';
        $actual = $browse->getThumbUrlFromImageUrl($img_url, $img_fn);
        $this->assertEquals($expected, $actual);
    }
    public function testGetThumbUrlAddsImageUrlToQueueIfThumbFileNotFound()
    {
        $browse = new Browse($this->config);
        $img_fn = $this->testFileDir . '/images/blog-2.jpg';
        $img_url = '/images/blog-2.jpg';
        $expected = TRUE;
        $browse->getThumbUrlFromImageUrl($img_url, $img_fn);
        $actual = in_array($img_fn, $browse->queue);
        $this->assertEquals($expected, $actual);
    }
    public function testGetListOfImagesReturnsArrayIterator()
    {
        $browse = new Browse($this->config);
        $browse->img_dir = $this->testImgDir;
        $images = $browse->getListOfImages($this->testImgDir);
        $expected = 'ArrayIterator';
        $actual = get_class($images);
        $this->assertEquals($expected, $actual, 'Browse::getListOfImages() did not return ArrayIterator instance');
    }
    public function testGetListOfImagesReturnsCorrectKey()
    {
        $browse = new Browse($this->config);
        $browse->img_dir = $this->testImgDir;
        $browse->allowed    = ['jpg'];
        $images = $browse->getListOfImages($this->testImgDir);
        $images->rewind();
        $expected = '/images/blog-1.jpg';
        $actual = $images->key();
        $this->assertEquals($expected, $actual, 'Browse::getListOfImages() did not return expected key');
    }
    public function testGetListOfImagesReturnsCorrectValue()
    {
        $browse = new Browse($this->config);
        $browse->img_dir = $this->testImgDir;
        $browse->allowed    = ['jpg'];
        $images = $browse->getListOfImages($this->testImgDir);
        $images->rewind();
        $expected = $this->testImgDir . '/blog-1.jpg';
        $actual = $images->current();
        $this->assertEquals($expected, $actual, 'Browse::getListOfImages() did not return expected value');
    }
    public function testGetListOfImagesReturnsCorrectCount()
    {
        $browse = new Browse($this->config);
        $browse->img_dir = $this->testImgDir;
        $images = $browse->getListOfImages($this->testImgDir);
        $expected = count(glob($this->testImgDir . '/*.jpg'))
                    + count(glob($this->testImgDir . '/*.png'))
                    + count(glob($this->testImgDir . '/test/*.png'))
                    + count(glob($this->testImgDir . '/test/*.jpg'));
        $actual = $images->count();
        $this->assertEquals($expected, $actual, 'Browse::getListOfImages() did not return expected count');
    }
    public function testGetListOfImagesReturnsCorrectCountIfOnlyPngAllowed()
    {
        $browse = new Browse($this->config);
        $browse->img_dir = $this->testImgDir;
        $browse->allowed    = ['png'];
        $images = $browse->getListOfImages($this->testImgDir);
        $expected = count(glob($this->testImgDir . '/*.png'));
        $actual = $images->count();
        $this->assertEquals($expected, $actual, 'Browse::getListOfImages() did not return expected value when only PNG allowed');
    }
    public function testGetListOfImagesExcludesFromPath()
    {
        $browse = new Browse($this->config);
        $browse->img_dir = $this->testImgDir;
        $browse->path_exclude    = ['xyz'];
        $images = $browse->getListOfImages($this->testImgDir);
        $expected = count(glob($this->testImgDir . '/*'));
        $actual = $images->count() + 1;
        $this->assertEquals($expected, $actual);
    }
    public function testMakeThumbnailCreatesImageReturnsFalseIfImageDoesntExist()
    {
        $img_base = '/doesnt_exist.jpg';
        $img_fn  = $this->testImgDir . $img_base;
        $browse = new Browse($this->config);
        $browse->img_dir = $this->testImgDir;
        $browse->allowed    = ['jpg'];
        $result = $browse->makeThumbnail($img_fn);
        $expected = FALSE;
        $actual   = $result;
        $this->assertEquals($expected, $actual, 'Browse::makeThumbnail() does not return FALSE if image file does not exist');
    }
    public function testMakeThumbnailCreatesImage()
    {
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        $img_base = '/blog-1.jpg';
        $img_fn  = $this->testImgDir . $img_base;
        $thumb_dir = $this->testFileDir . '/thumb';
        $thumb_fn  = $thumb_dir . $img_base;
        if (file_exists($thumb_fn)) unlink($thumb_fn);
        $browse = new Browse($this->config);
        $browse->img_dir = $this->testImgDir;
        $browse->thumb_dir  = $thumb_dir;
        $browse->allowed    = ['jpg'];
        $result = $browse->makeThumbnail($img_fn);
        $list   = glob($thumb_fn);
        $expected = $thumb_fn;
        $actual   = $list[0] ?? '';
        $this->assertEquals($expected, $actual, 'Browse::makeThumbnail() did not create thumbnail image');
    }
    public function testMakeThumbnailCreatesImageSubdir()
    {
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        $img_base = '/test/test.jpg';
        $thumb_dir = $this->testFileDir . '/thumb';
        $img_fn  = $this->testImgDir . $img_base;
        $thumb_fn  = $thumb_dir . $img_base;
        $thumb_sub = dirname($thumb_fn);
        if (file_exists($thumb_fn)) unlink($thumb_fn);
        if (file_exists($thumb_sub)) rmdir($thumb_sub);
        $browse = new Browse($this->config);
        $browse->img_dir = $this->testImgDir;
        $browse->thumb_dir  = $thumb_dir;
        $result = $browse->makeThumbnail($img_fn);
        $list   = glob($thumb_fn);
        $expected = $thumb_fn;
        $actual   = $list[0] ?? '';
        $this->assertEquals(TRUE, file_exists($thumb_sub), 'Browse::makeThumbnail() did not create thumbnail subdirectory');
        $this->assertEquals($expected, $actual, 'Browse::makeThumbnail() did not create thumbnail image in subdirectory');
    }
    public function testHandleReturnsSameCountAsNumberOfImages()
    {
        $browse = new Browse($this->config);
        $browse->img_dir = $this->testImgDir;
        $browse->allowed    = ['png'];
        $generator = $browse->handle();
        $expected = count(glob($this->testImgDir . '/*.png'));
        $actual = 0;
        foreach ($generator as $item)
            $actual += (strpos($item, 'input')) ? 1 : 0;
        $this->assertEquals($expected, $actual, 'Browse::handle() did not return expected number of image references when only PNG allowed');
    }
}
