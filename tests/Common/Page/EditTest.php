<?php
namespace SimpleHtmlTest\Common\Page;

use SimpleHtml\Common\Page\Edit;
use PHPUnit\Framework\TestCase;
class EditTest extends TestCase
{
    public $edit;
    public $testFileDir = '';
    public function setUp() : void
    {
        $this->testFileDir = realpath(__DIR__ . '/../../test_files');
        $config = include __DIR__ . '/../../../src/config/config.php';
        $this->edit = new Edit($config);
    }
    public function testGetKeyFromURL()
    {
        $url = 'https://unlikelysource.com/test1';
        $expected = '/test1';
        $actual   = $this->edit->getKeyFromURL($url, $this->testFileDir);
        $this->assertEquals($expected, $actual, 'Edit::getKeyFromURL() does not produce expected key');
    }
    public function testGetKeyFromFilename()
    {
        $fn  = $this->testFileDir . '/test1.html';
        $expected = '/test1';
        $actual   = $this->edit->getKeyFromFilename($fn, $this->testFileDir);
        $this->assertEquals($expected, $actual, 'Edit::getKeyFromFilename() does not produce expected key');
    }
    public function testGetListOfPagesCreatesArray()
    {
        $pages = $this->edit->getListOfPages($this->testFileDir);
        $expected = TRUE;
        $actual   = (is_array($pages) && !empty($pages));
        $this->assertEquals($expected, $actual, 'Edit::getListOfPages() did not produce array');
    }
    public function testGetListOfPagesIncludesOnlyHtmlFiles()
    {
        $pages = $this->edit->getListOfPages($this->testFileDir);
        $expected = count(glob($this->testFileDir . '/*.htm*'));
        $actual   = count($pages);
        $this->assertEquals($expected, $actual, 'Edit::getListOfPages() did not find only HTML files');
    }
    public function testGetListOfPagesIncludesTest1()
    {
        $pages = $this->edit->getListOfPages($this->testFileDir);
        $expected = realpath($this->testFileDir . '/test1.html');
        $actual   = $pages['/test1'] ?? '';
        $this->assertEquals($expected, $actual, 'Edit::getListOfPages() did not include full path to test1.html');
    }
    public function testGetListOfPagesIncludesHtmFiles()
    {
        $pages = $this->edit->getListOfPages($this->testFileDir);
        $expected = realpath($this->testFileDir . '/test4.htm');
        $actual   = $pages['/test4'] ?? '';
        $this->assertEquals($expected, $actual, 'Edit::getListOfPages() did not include full path to test4.htm');
    }
    public function testGetListOfPagesDoesNotIncludesPhtmlFiles()
    {
        $pages = $this->edit->getListOfPages($this->testFileDir);
        $expected = TRUE;
        $actual   = empty($pages['/not_found']);
        $this->assertEquals($expected, $actual, 'Edit::getListOfPages() should not include PHTML files');
    }
    public function testGetContentsFromPage()
    {
        $expected = file_get_contents($this->testFileDir . '/test1.html');
        $actual   = $this->edit->getContentsFromPage('/test1', $this->testFileDir);
        $this->assertEquals($expected, $actual, 'Edit::getContentsFromPage() did not return expected HTML');
    }
}
