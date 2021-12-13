<?php
namespace FileCMSTest\Common\Import;

use FileCMS\Common\Generic\Messages;
use FileCMS\Common\Import\Import;
use FileCMS\Common\Page\Edit;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
class BulkImportTest extends TestCase
{
    public $config = [];
    public $testFileDir = '';
    public $testBackupDir = '';
    public function setUp() : void
    {
        $this->config = include BASE_DIR . '/tests/config/test.config.php';
        $this->testFileDir = realpath(__DIR__ . '/../../test_files');
        $this->testBackupDir = realpath(__DIR__ . '/../../backups');
        $path = $this->testFileDir . '/bulk';
        if (!file_exists($path)) {
            mkdir($path, 0775, TRUE);
        } else {
            $list = glob($path . '/*');
            if (!empty($list))
                foreach ($list as $fn) unlink($fn);
        }
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
        $backup_dir  = $this->testBackupDir;
        $bulk        = [];
        $temp = <<<EOT
https://test.unlikelysource.com/test1.html
https://test.unlikelysource.com/test2.html
https://test.unlikelysource.com/test3.html
EOT;
        $list = explode(PHP_EOL, trim($temp));
        $result = Import::do_bulk_import($list, $trusted, $transform, $delim_start, $delim_stop, $edit, $message, $backup_dir, $path);
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
