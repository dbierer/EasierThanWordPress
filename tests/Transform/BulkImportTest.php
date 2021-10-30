<?php
namespace EasierThanWordPressTest\Transform;

use EasierThanWordPress\Common\Generic\Messages;
use EasierThanWordPress\Transform\Import;
use EasierThanWordPress\Common\Page\Edit;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
class BulkImportTest extends TestCase
{
    public $config = [];
    public $testFileDir = '';
    public function setUp() : void
    {
        $this->config = require __DIR__ . '/../../src/config/config.php';
        $this->testFileDir = realpath(__DIR__ . '/../test_files');
        $path = $this->testFileDir . '/bulk';
        $list = glob($path . '/*');
        if (!empty($list))
            foreach ($list as $fn) unlink($fn);
    }
    public function testBulkImportCreatesExpectedFiles()
    {
        $edit        = new Edit($this->config);
        $transform   = $this->config['IMPORT']['transform'];
        $delim_start = $this->config['IMPORT']['delim_start'];
        $delim_stop  = $this->config['IMPORT']['delim_stop'];
        $trusted     = ['https://test.unlikelysource.com'];
        $message     = Messages::getInstance();
        $path        = $this->testFileDir . '/bulk';
        $bulk        = [];
        $temp = <<<EOT
https://test.unlikelysource.com/test1.html
https://test.unlikelysource.com/test2.html
https://test.unlikelysource.com/test3.html
EOT;
        $list = explode(PHP_EOL, trim($temp));
        $result = Import::do_bulk_import($list, $trusted, $transform, $delim_start, $delim_stop, $edit, $message, $path);
        $expected = [
            $path . '/test1.html',
            $path . '/test2.html',
            $path . '/test3.html',
        ];
        $actual = glob($path . '/*');
        $this->assertEquals($expected, $actual, 'Bulk import failed to create files');
        $expected = '<h1>Test 1</h1>';
        $actual   = file_get_contents($path . '/test1.html');
        $this->assertEquals($expected, $actual, 'Bulk import failed to write correct content');
    }
}
