<?php
namespace FileCMSTest\Common\Data;

use FileCMS\Common\Data\Csv;
use PHPUnit\Framework\TestCase;
class CsvTest extends TestCase
{
    public $csv = NULL;
    public $csvFn = '';
    public $csvFileDir = __DIR__ . '/../../logs';
    public $headers = ['add_on_plan','msmf_listed','import_url','web_person_email','web_person_name','first_name','last_name','degree','dentist_email','gender','smart_cert','source','order_date'];
    public function setUp() : void
    {
        $this->csvFn = $this->csvFileDir . '/order.csv';
        $this->csv = new Csv($this->csvFn);
        $csv_fn = $this->csvFileDir . '/test.csv';
        if (file_exists($csv_fn)) unlink($csv_fn);
    }
    //     public function __construct(string $csv_fn)
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
    //     public function getItemsFromCsv($key_field = NULL) : array
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
    //     public static function array2csv(array $data) : string
    public function testArray2csv()
    {
        $arr = ['AAA',111,222.222,'This is a "test"',TRUE];
        $expected = 'AAA,111,222.222,"This is a ""test""",1';
        $actual   = Csv::array2csv($arr);
        $this->assertEquals($expected, $actual);
    }
    //     public function writeRowToCsv(array $post, array $csv_fields, bool $first = TRUE) : bool
    public function testWriteRowToCsvWritesHeadersIfFileBlank()
    {
        $csv_fn = $this->csvFileDir . '/test.csv';
        $arr = ['silver','already_listed','https://unlikelysource.com','test@unlikelysource.com','Barney Rubble','Fred','Flintstone','LSD','doug@unlikelysource.com','M','0','https://mercurysafedentistry.com/order','2022-10-06 22:19:40'];
        $arr = array_combine($this->headers, $arr);
        $csv = new Csv($csv_fn);
        $csv->writeRowToCsv($arr, $this->headers);
        $lines = file($csv_fn);
        $expected = 2;
        $actual   = count($lines);
        $this->assertEquals($expected, $actual);
        $expected = Csv::array2csv($this->headers);
        $actual   = trim($lines[0]);
        $this->assertEquals($expected, $actual);
    }
    public function testWriteRowToCsvWritesColumnsInOrder()
    {
        $csv_fn = $this->csvFileDir . '/test.csv';
        $arr = ['silver','already_listed','https://unlikelysource.com','test@unlikelysource.com','Barney Rubble','Fred','Flintstone','LSD','doug@unlikelysource.com','M','0','https://mercurysafedentistry.com/order','2022-10-06 22:19:40'];
        $arr = array_combine($this->headers, $arr);
        $csv = new Csv($csv_fn);
        $csv->writeRowToCsv($arr, $this->headers);
        $lines = file($csv_fn);
        $expected = $arr;
        $actual   = array_combine(str_getcsv($lines[0]), str_getcsv($lines[1]));
        $this->assertEquals($expected, $actual);
    }
    //     public function findItemInCSV(string $search, bool $case = FALSE, bool $first = TRUE) : array
    public function testFindItemInCsvPopulatesLines()
    {
        $search   = 'BETTY@UNLIKELYSOURCE.COM';
        $this->csv->findItemInCSV($search, FALSE, TRUE);
        $expected = file($this->csvFn);
        $actual   = $this->csv->lines;
        $this->assertEquals($expected, $actual);
    }
    public function testFindItemInCsvPopulatesHeaders()
    {
        $search   = 'BETTY@UNLIKELYSOURCE.COM';
        $this->csv->findItemInCSV($search, FALSE, TRUE);
        $expected = $this->headers;
        $actual   = $this->csv->headers;
        $this->assertEquals($expected, $actual);
    }
    public function testFindItemInCsvSetLinePointer()
    {
        $search   = 'BETTY@UNLIKELYSOURCE.COM';
        $this->csv->findItemInCSV($search, FALSE, TRUE);
        $lines = file($this->csvFn);
        $expected = $lines[3];
        $actual   = $this->csv->lines[$this->csv->pos];
        $this->assertEquals($expected, $actual);
    }
    /*
    public function testFindItemInCsvCaseInsensitive()
    {
        $csv_fn = $this->csvFileDir . '/test.csv';
        $arr = ['silver','already_listed','https://unlikelysource.com','test@unlikelysource.com','Barney Rubble','Fred','Flintstone','LSD','doug@unlikelysource.com','M','0','https://mercurysafedentistry.com/order','2022-10-06 22:19:40'];
        $arr = array_combine($this->headers, $arr);
        $csv = new Csv($csv_fn);
        $csv->writeRowToCsv($arr, $this->headers);
        $search = 'Barney Rubble';
        $expected = $arr;
        $actual   = $csv->findItemInCSV($search);
        $this->assertEquals($expected, $actual);
    }
    public function testFindItemInCsvCaseSensitiveReturnsFalse()
    {
        $search   = 'BETTY@UNLIKELYSOURCE.COM';
        $expected = [];
        $actual   = $this->csv->findItemInCSV($search, TRUE, TRUE);
        $this->assertEquals($expected, $actual);
    }
    public function testFindItemInCsvTreatsFirstRowAsDataIfFirstFlagFalse()
    {
        $search   = 'web_person_email';
        $expected = $this->headers;
        $actual   = $this->csv->findItemInCSV($search, FALSE, FALSE);
        $this->assertEquals($expected, $actual);
    }
    //     public function updateRowInCsv(string $search, array $data, array $csv_fields = [], bool $case = FALSE) : bool
    public function testUpdateRowInCsv()
    {
        $csv_fn = $this->csvFileDir . '/test.csv';
        $arr = ['silver','already_listed','https://unlikelysource.com','test@unlikelysource.com','Barney Rubble','Fred','Flintstone','LSD','doug@unlikelysource.com','M','0','https://mercurysafedentistry.com/order','2022-10-06 22:19:40'];
        $arr = array_combine($this->headers, $arr);
        $csv = new Csv($csv_fn);
        $csv->writeRowToCsv($arr, $this->headers);
        $search = 'Barney Rubble';
        $replace = ['web_person_email' => 'pebbles@flintstone.com','web_person_name' => 'Pebbles Flintstone'];
        $expected = TRUE;
        $actual   = $csv->updateRowInCsv($search, $replace, $this->headers, FALSE);
        $this->assertEquals($expected, $actual);
        $lines = file($csv_fn);
        $row   = array_combine(str_getcsv($lines[0]), str_getcsv($lines[1]));
        $expected = '';
        $actual   = $row['web_person_email'] ?? 'XXX';
        $this->assertEquals($expected, $actual);
        $expected = '';
        $actual   = $row['web_person_name'] ?? 'XXX';
        $this->assertEquals($expected, $actual);
    }
    */
}
