<?php
namespace SimpleHtmlTest\Transform;

use SimpleHtml\Common\Generic\Messages;
use SimpleHtml\Transform\Import;
use SimpleHtml\Common\Page\Edit;
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
        $result = $this->run_bulk_import();
        $path = $this->testFileDir . '/bulk';
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
    protected function run_bulk_import()
    {
        $edit        = new Edit($this->config);
        $transform   = $this->config['IMPORT']['transform'] ?? [];
        $delim_start = $this->config['IMPORT']['delim_start'];
        $delim_stop  = $this->config['IMPORT']['delim_stop'];
        $trusted     = ['http://test.unlikelysource.com'];
        $message     = Messages::getInstance();
        $bulk        = [];
        $temp = <<<EOT
http://test.unlikelysource.com/test1.html
http://test.unlikelysource.com/test2.html
http://test.unlikelysource.com/test3.html
EOT;
        $list = explode(PHP_EOL, trim($temp));
        foreach ($list as $url) {
            $url  = strip_tags($url);
            if (Import::is_trusted($url, $trusted)) {
                echo "\nProcessing: $url";
                $key = $this->do_import($url, $transform, $delim_start, $delim_stop, $edit, $message);
                if ($key !== FALSE) $bulk[] = $key;
            }
        }
        echo "\n";
        return $bulk;
    }
    protected function do_import($url, $transform, $delim_start, $delim_stop, $edit, $message)
    {
        set_time_limit(30);
        $ok   = FALSE;
        $html = Import::import($url, $transform, $delim_start, $delim_stop);
        $key  = $edit->getKeyFromURL($url);
        $path = $this->testFileDir . '/bulk';
        if ($edit->save($key, $html, $path, TRUE)) {
            $message->addMessage(Edit::SUCCESS_SAVE);
            $ok = TRUE;
        } else {
            $message->addMessage(Edit::ERROR_SAVE);
        }
        return ($ok) ? $key : FALSE;
    }
}
