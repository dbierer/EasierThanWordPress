<?php
namespace SimpleHtmlTest\Transform;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SimpleHtml\Transform\Import;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
class ImportTest extends TestCase
{
    public $testFileDir = '';
    public function setUp() : void
    {
        $this->testFileDir = realpath(__DIR__ . '/../test_files');
    }
    public function testGetDelimitedStartDoesNotExist()
    {
        $text     = '<html><body><p>xxx</p></body></html>';
        $start    = '<div>';
        $expected = $text;
        $actual = Import::get_delimited($text, $start);
        $this->assertEquals($expected, $actual, 'Contents from between delimiters not returned when start does not exist');
    }
    public function testGetDelimitedNoStop()
    {
        $text     = '<html><body><p>xxx</p></body></html>';
        $start    = '<body>';
        $expected = '<p>xxx</p></body></html>';
        $actual = Import::get_delimited($text, $start);
        $this->assertEquals($expected, $actual, 'Contents from between delimiters not returned with no stop');
    }
    public function testGetDelimitedExtractsExpectedContent()
    {
        $text     = "<html>\n<body>\n<div class='xxx'><p>xxx</p>\n</div>\n</body>\n</html>\n";
        $start    = "<div class='xxx'>";
        $stop     = '</div>';
        $expected = '<p>xxx</p>';
        $actual = Import::get_delimited($text, $start, $stop);
        $this->assertEquals($expected, $actual, 'Contents from between delimiters not returned.');
    }
    public function testIsTrustedReturnsTrueAsExpected()
    {
        $url = 'https://test.unlikelysource.com/test1.html';
        $trusted = ['https://test.unlikelysource.com'];
        $expected = TRUE;
        $actual = Import::is_trusted($url, $trusted);
        $this->assertEquals($expected, $actual, 'is_trusted is not working');
    }
    public function testIsTrustedReturnsFalseAsExpected()
    {
        $url = 'https://bad.com/test1.html';
        $trusted = ['https://test.unlikelysource.com'];
        $expected = FALSE;
        $actual = Import::is_trusted($url, $trusted);
        $this->assertEquals($expected, $actual, 'is_trusted is not working');
    }
    public function testImportExtractsExpectedContentWithNoCallbacks()
    {
        $url      = 'http://test.unlikelysource.com/test1.html';
        $callbax  = [];
        $start    = '<body>';
        $stop     = '</body>';
        $expected = '<h1>Test 1</h1>';
        echo "\nMaking request to $url\n";
        $actual = Import::import($url, $callbax, $start, $stop);
        $this->assertEquals($expected, $actual, 'Contents from between delimiters not returned.');
    }
    public function testImportExtractsExpectedContentWithStrToUpperCallback()
    {
        $func     = function ($txt, $args) { return strtoupper($txt); };
        $url      = 'http://test.unlikelysource.com/test1.html';
        $callbax  = ['upper' => ['callback' => $func, 'params' => []]];
        $start    = '<body>';
        $stop     = '</body>';
        $expected = '<H1>TEST 1</H1>';
        echo "\nMaking request to $url\n";
        $actual = Import::import($url, $callbax, $start, $stop);
        $this->assertEquals($expected, $actual, 'Callback not invoked properly');
    }
    /*
    public function testGetUpload()
    {
        // generate list of URLs
        $trusted  = ['http://unlikelysource.com'];
        $src_name = $this->testFileDir . '/files_to_upload.txt';
        $tmp_name = $this->testFileDir . '/php' . bin2hex(random_bytes(3));
        copy($src_name, $tmp_name);
        $size     = filesize($tmp_name);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_FILES['test'] = [
            'name' => 'test.txt',
            'type' => 'text/html',
            'tmp_name' => $tmp_name,
            'error' => '0',
            'size' => $size
        ];
        $expected = 2;
        $actual   = count(Import::get_upload('test', $_FILES, $trusted));
        $this->assertEquals($expected, $actual, 'Upload count does not match');
    }
    */
}
