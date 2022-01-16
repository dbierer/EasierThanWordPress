<?php
namespace FileCMSTest\Common\Data;

use FileCMS\Common\Data\Storage;
use PHPUnit\Framework\TestCase;
class StorageBase extends TestCase
{
    public $testImgFileList = [];
    public $testFileDir = '';
    public $testStorageDir = '';
    public $config = [];
    public $storage = NULL;
    public static $tmpFn = '';
    public function setUp() : void
    {
        $this->testFileDir = realpath(__DIR__ . '/../../test_files');
        $this->testStorageDir = realpath(__DIR__ . '/../../data');
        $this->testImgFileList = file($this->testFileDir . '/list_of_images.txt');
        self::$tmpFn = tempnam($this->testStorageDir, 'test_');
        $this->config = include BASE_DIR . '/tests/config/test.config.php';
        $this->config['STORAGE']['storage_fn']  = self::$tmpFn;
        $this->config['STORAGE']['storage_dir'] = $this->testStorageDir;
        $this->storage = new Storage($this->config);
    }
    public function tearDown() : void
    {
        if (file_exists(self::$tmpFn)) unlink(self::$tmpFn);
    }
}
