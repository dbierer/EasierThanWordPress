<?php
namespace SimpleHtmlTest\Common\Page;

use SimpleHtml\Common\Page\Edit;
use PHPUnit\Framework\TestCase;
class EditTest extends TestCase
{
    public $edit;
    public $testFileDir = '';
    public $new_dir = '';
    public function setUp() : void
    {
        $this->testFileDir = realpath(__DIR__ . '/../../test_files');
        $config = include __DIR__ . '/../../../src/config/config.php';
        $this->edit = new Edit($config);
        $this->new_dir = $this->testFileDir . '/new';
        $new = glob($this->new_dir . '/*');
        if (!empty($new))
            foreach ($new as $fn) unlink($fn);

        if (file_exists($this->new_dir)) rmdir($this->new_dir);
    }
    public function testGetKeyFromURL()
    {
        $url = 'https://unlikelysource.com/test1';
        $expected = '/test1';
        $actual   = $this->edit->getKeyFromURL($url, $this->testFileDir);
        $this->assertEquals($expected, $actual, 'Edit::getKeyFromURL() does not produce expected key');
    }
    public function testGetKeyFromURLGetsRidOfExtension()
    {
        $url = 'https://unlikelysource.com/test1.html';
        $expected = '/test1';
        $actual   = $this->edit->getKeyFromURL($url, $this->testFileDir);
        $this->assertEquals($expected, $actual, 'Edit::getKeyFromURL() does not remove .html extension');
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
        $expected += count(glob($this->testFileDir . '/sub/*.htm*'));
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
    public function testGetPageFromURL()
    {
        $expected = file_get_contents($this->testFileDir . '/test1.html');
        $actual   = $this->edit->getPageFromURL('https://unlikelysource.com/test1', $this->testFileDir);
        $this->assertEquals($expected, $actual, 'Edit::getPageFromURL() did not return expected HTML');
    }
    public function testGetListOfPagesDoesNotFindsNonExistentFile()
    {
        $fn = $this->testFileDir . '/testX.html';
        if (file_exists($fn)) unlink($fn);
        $this->edit->pages = [];
        $pages = $this->edit->getListOfPages($this->testFileDir);
        $expected = TRUE;
        $actual   = (empty($pages['/textX']));
        $this->assertEquals($expected, $actual, 'Edit::getListOfPages() listed a file that does not exist');
    }
    public function testGetListOfPagesFindsNewFile()
    {
        $fn = $this->testFileDir . '/testX.html';
        copy($this->testFileDir . '/test1.html', $fn);
        $this->edit->pages = [];
        $pages = $this->edit->getListOfPages($this->testFileDir);
        $expected = $this->testFileDir . '/testX.html';
        $actual   = $pages['/testX'] ?? '';
        $this->assertEquals($expected, $actual, 'Edit::getListOfPages() failed to list a new file');
    }
    public function testSaveOverwritesExistingSuccessfully()
    {
        $contents = file_get_contents($this->testFileDir . '/test1.html');
        file_put_contents($this->testFileDir . '/testX.html', $contents);
        $contents = str_replace('Test 1', 'Test X', $contents);
        $expected = TRUE;
        $actual   = $this->edit->save('/testX', $contents, $this->testFileDir);
        $this->assertEquals($expected, $actual, 'Edit::save() did not return TRUE upon successful save');
    }
    public function testSaveOverwritesExistingContents()
    {
        $contents = file_get_contents($this->testFileDir . '/test1.html');
        file_put_contents($this->testFileDir . '/testX.html', $contents);
        $contents = str_replace('Test 1', 'Test X', $contents);
        $response = $this->edit->save('/testX', $contents, $this->testFileDir);
        $expected = $contents;
        $actual   = file_get_contents($this->testFileDir . '/testX.html');
        $this->assertEquals($expected, $actual, 'Edit::save() did overwrite original');
    }
    public function testSaveFixesUsingTidy()
    {
        $contents = file_get_contents($this->testFileDir . '/test1.html');
        file_put_contents($this->testFileDir . '/testX.html', $contents);
        $contents = str_replace('Test 1', 'Test X', $contents);
        $response = $this->edit->save('/testX', $contents, $this->testFileDir, TRUE);
        $expected = '<h1>Test X</h1>';
        $actual   = trim(file_get_contents($this->testFileDir . '/testX.html'));
        $this->assertEquals($expected, $actual, 'Edit::save() did not fix using Tidy');
    }
    public function testSaveCreatesNewFile()
    {
        $new_fn   = $this->testFileDir . '/testY.html';
        if (file_exists($new_fn)) unlink($new_fn);
        $contents = file_get_contents($this->testFileDir . '/test1.html');
        $contents = str_replace('Test 1', 'Test Y', $contents);
        $response = $this->edit->save('/testY', $contents, $this->testFileDir);
        $expected = $contents;
        $actual   = (file_exists($new_fn)) ? file_get_contents($new_fn) : '';
        $this->assertEquals($expected, $actual, 'Edit::save() did not save new file');
    }
    public function testSaveCreatesNewFileAndNewPath()
    {
        $new_fn   = $this->new_dir . '/testZ.html';
        $contents = file_get_contents($this->testFileDir . '/test1.html');
        $contents = str_replace('Test 1', 'Test Z', $contents);
        $response = $this->edit->save('/new/testZ', $contents, $this->testFileDir);
        $expected = TRUE;
        $actual   = (file_exists($new_fn));
        $this->assertEquals($expected, $actual, 'Edit::save() did not create new directory and save new file');
    }
    public function testSaveCreatesNewFileAndNewPathAndContentsMatch()
    {
        $new_fn   = $this->new_dir . '/testZ.html';
        $contents = file_get_contents($this->testFileDir . '/test1.html');
        $contents = str_replace('Test 1', 'Test Z', $contents);
        $response = $this->edit->save('/new/testZ', $contents, $this->testFileDir);
        $expected = $contents;
        $actual   = (file_exists($new_fn)) ? file_get_contents($new_fn) : '';
        $this->assertEquals($expected, $actual, 'Edit::save() did not create new file in new directory contents do not match');
    }
    public function testDeleteReturnsTrueIfFileIsDeleted()
    {
        $new_fn   = $this->testFileDir . '/testZ.html';
        $contents = file_get_contents($this->testFileDir . '/test1.html');
        $contents = file_put_contents($new_fn, $contents);
        $expected = TRUE;
        $actual   = $this->edit->delete('/testZ', $this->testFileDir);
        $this->assertEquals($expected, $actual, 'Edit::delete() did not return TRUE if file is deleted');
    }
    public function testDeleteReturnsFalseIfFileNotDeleted()
    {
        $expected = FALSE;
        $actual   = $this->edit->delete('/doesnotexist', $this->testFileDir);
        $this->assertEquals($expected, $actual, 'Edit::delete() did not return FALSE if file not deleted');
    }
    public function testDeleteFileIsTrulyGone()
    {
        $new_fn   = $this->testFileDir . '/testQ.html';
        $contents = file_get_contents($this->testFileDir . '/test1.html');
        $contents = str_replace('Test 1', 'Test Q', $contents);
        $response = $this->edit->delete('/testQ', $this->testFileDir);
        $expected = FALSE;
        $actual   = (file_exists($new_fn));
        $this->assertEquals($expected, $actual, 'Edit::delete() did not delete file');
    }
}
