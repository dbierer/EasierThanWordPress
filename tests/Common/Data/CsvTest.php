<?php
namespace FileCMSTest\Common\Data;

use FileCMS\Common\Data\Csv;
use PHPUnit\Framework\TestCase;
class CsvTest extends TestCase
{
    public $csv = NULL;
    public $csvFn = '';
    public $csvFileDir = __DIR__ . '/../../logs';
    public function setUp() : void
    {
        $this->csvFn = $this->csvFileDir . '/order.csv';
        $this->csv = new Csv($this->csvFn);
    }
    public function testCsvCreatesZeroByteFileIfNew()
    {
        $csv_fn = $this->csvFileDir . '/test.csv';
        $expected = FALSE;
        $actual   = file_exists($csv_fn);
        $this->assertEquals($expected, $actual);
        $csv = new Csv($csv_fn);
        $expected = 0;
        $actual   = $csv->size;
        $this->assertEquals($expected, $actual);
        $expected = TRUE;
        $actual   = file_exists($csv_fn);
        $this->assertEquals($expected, $actual);
        unlink($csv_fn);
    }
    public function testGetItemsFromCsvReturnsNumericArrayIfKeyFieldBlank()
    {
        $rows = $this->csv->getItemsFromCsv();
        next($rows);
        $expected = 1;
        $actual   = key($rows);
        $this->assertEquals($expected, $actual);
    }
    public function testGetItemsFromCsvReturnsExpectedNumberOfRows()
    {
        $lines = file($this->csvFn);
        $rows = $this->csv->getItemsFromCsv();
        $expected = count($lines);
        $actual   = count($rows);
        $this->assertEquals($expected, $actual);
    }
    public function testGetItemsFromCsvReturnsExpectedAssocArray()
    {
        $rows = $this->csv->getItemsFromCsv('dentist_email');
        $expected = 'Wilma';
        $actual   = $rows['wilma@flintstone.com']['first_name'];
        $this->assertEquals($expected, $actual);
    }
    public function testGetItemsFromCsvReturnsExpectedAssocArrayIfArrayKeyField()
    {
        $rows = $this->csv->getItemsFromCsv(['first_name','last_name']);
        $expected = 'wilma@flintstone.com';
        $actual   = $rows['Wilma_Flintstone']['dentist_email'];
        $this->assertEquals($expected, $actual);
    }
}
