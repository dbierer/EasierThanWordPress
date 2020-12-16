<?php
// based upon article by Daniel Opitz (https://odan.github.io/2017/08/16/create-a-php-phar-file.html)
// location of directory containing code to be included in phar file
$path = $argv[1] ?? '';

if (!$path || !file_exists($path)) {
    echo "Usage: php create_phar.php /path/to/project\n";
    exit;
}

// The php.ini setting phar.readonly must be set to 0
$pharFile = 'phpcl-lfc-tools.phar';

// clean up
if (file_exists($pharFile)) {
    unlink($pharFile);
}
if (file_exists($pharFile . '.gz')) {
    unlink($pharFile . '.gz');
}

// create phar
$p = new Phar($pharFile, 0, $pharFile);

// creating our library using whole directory
if (substr($path, -1) == DIRECTORY_SEPARATOR) {
    $path = substr($path, 0, -1);
}

// produce iteration which excludes any references to values assigned to $excludes
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
);
$filter = new class($iterator) extends FilterIterator {
    public static $excludes = [];
    public function accept()
    {
        $item = $this->current();
        $actual   = 0;
        foreach (self::$excludes as $exclude) {
            $actual += (int) boolval(strpos($item->getPath(), $exclude));
        }
        return ($actual === 0);
    }
};
$filter::$excludes = ['.git'];

// build iteration using relative paths only
$list = [];
foreach ($filter as $name => $obj) {
    $key = substr(str_replace($path, '', $name), 1);
    $list[$key] = $name;
}

$p->buildFromIterator(new ArrayIterator($list), $path);

// pointing main file which requires all classes
$p->setDefaultStub('index.php');

// plus - compressing it into gzip
$p->compress(Phar::GZ);

// clean up
if (file_exists($pharFile . '.gz')) {
    unlink($pharFile . '.gz');
}

echo "$pharFile successfully created\n";
echo "Usage: php $pharFile\n";
