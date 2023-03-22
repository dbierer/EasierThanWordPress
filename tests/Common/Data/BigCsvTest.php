<?php
namespace FileCMSTest\Common\Data;

use FileCMS\Common\Data\BigCsv;
use PHPUnit\Framework\TestCase;
class BigCsvTest extends TestCase
{
    public $csv = NULL;
    public $csvFn = '';
    public $csvTestFn = '';
    public $csvFileDir = __DIR__ . '/../../logs';
    public $tmp_fn = '';
    public $headers = [];
    public $date = '';
    public $test_arr = [];
    public function setUp() : void
    {
        $this->date = date('Y-m-d H:i:s');
        $this->csvFn = $this->csvFileDir . '/order.csv';
        $this->csvTestFn = $this->csvFileDir . '/test.csv';
        $this->tmp_fn = $this->csvFileDir . '/temp.csv';
        $this->csv = new BigCsv($this->csvFn);
		$this->test_arr = ['test','test','https://unlikelysource.com','test@unlikelysource.com','Barney Rubble','Testy','Tester','LSD','testy@unlikelysource.com','M','0','https://mercurysafedentistry.com/order',$this->date];
        // populate headers
        $lines = file($this->csvFn);
        $this->headers = str_getcsv($lines[0]);
        // get rid of test.csv and temp.csv
        if (file_exists($this->csvTestFn)) unlink($this->csvTestFn);
        if (file_exists($this->tmp_fn)) unlink($this->tmp_fn);
    }
    // __construct(string $csv_fn, array $headers = [])
    public function testConstructWritesHeadersIfGiven()
    {
        $csv_fn = $this->csvTestFn;
        $arr = $this->test_arr;
        $csv = new BigCsv($csv_fn, $arr);
        unset($csv);
        $expected = $arr;
        $actual = str_getcsv(file($csv_fn)[0]);
        $this->assertEquals($expected, $actual);
    }
    public function testConstructCreatesEmptyFileIfNoHeadersGiven()
    {
        $csv_fn = $this->csvTestFn;
        $csv = new BigCsv($csv_fn);
        $expected = 0;
        $actual = $csv->size;
        $this->assertEquals($expected, $actual, '$size property non-zero');
        unset($csv);
        $expected = TRUE;
        $actual = file_exists($csv_fn);
        $this->assertEquals($expected, $actual);
    }
    // Csv::getItemsFromCsv($key_field = NULL, bool $first_row = TRUE) : array
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
        $expected = count(file($this->csvFn));
        $actual   = count($this->csv->getItemsFromCsv());
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
    // findItemInCSV(string $search, bool $case = FALSE, bool $first = TRUE) : array
    public function testFindItemInCsvPopulatesHeaders()
    {
        $search   = 'BETTY@UNLIKELYSOURCE.COM';
        $this->csv->findItemInCSV($search, FALSE, TRUE);
        $expected = $this->headers;
        $actual   = $this->csv->headers;
        $this->assertEquals($expected, $actual);
    }
    public function testFindItemInCsvCaseInsensitive()
    {
        $search   = 'BETTY@UNLIKELYSOURCE.COM';
        $row = $this->csv->findItemInCSV($search, FALSE, TRUE);
        $expected = strtolower($search);
        $actual   = $row['dentist_email'];
        $this->assertEquals($expected, $actual);
    }
    public function testFindItemInCsvCaseSensitiveReturnsEmptyArray()
    {
        $search   = 'BETTY@UNLIKELYSOURCE.COM';
        $row = $this->csv->findItemInCSV($search, TRUE, TRUE);
        $expected = [];
        $actual   = $row;
        $this->assertEquals($expected, $actual);
    }
    public function testFindItemInCsvTreatsFirstRowAsDataIfFirstFlagFalse()
    {
        $search   = 'web_person_email';
        $expected = $this->headers;
        $actual   = $this->csv->findItemInCSV($search, FALSE, FALSE);
        $this->assertEquals($expected, $actual);
    }
    // writeRowToCsv(array $post, array $csv_fields = []) : bool
    public function testWriteRowToCsvDoesNotWriteHeadersIf2ndArgEmpty()
    {
        $csv_fn = $this->csvTestFn;
        $arr = $this->test_arr;
        $arr = array_combine($this->headers, $arr);
        $csv = new BigCsv($csv_fn);
        $csv->writeRowToCsv($arr);
        $lines = file($csv_fn);
        $expected = array_values($arr);
        $actual   = str_getcsv($lines[0]);
        $this->assertEquals($expected, $actual);
    }
    public function testWriteRowToCsvWritesHeadersIfFileBlank()
    {
        $csv_fn = $this->csvTestFn;
        $arr = $this->test_arr;
        $arr = array_combine($this->headers, $arr);
        $csv = new BigCsv($csv_fn);
        $csv->writeRowToCsv($arr, $this->headers);
        $expected = 2;
        $actual   = count(file($csv_fn));
        $this->assertEquals($expected, $actual, 'Line count does not match');
    }
    public function testWriteRowToCsvUpdatesSize()
    {
        $csv_fn = $this->csvTestFn;
        $csv = new BigCsv($csv_fn);
        $expected = 0;
        $actual = $csv->size;
        $this->assertEquals($expected, $actual, 'Size should be zero');
        $arr = $this->test_arr;
        $arr = array_combine($this->headers, $arr);
        $csv->writeRowToCsv($arr, $this->headers);
        $expected = TRUE;
        $actual   = ($csv->size > 0);
        $this->assertEquals($expected, $actual, 'Size not updated');
    }
    public function testWriteRowToCsvWritesColumnsInOrder()
    {
        $csv_fn = $this->csvTestFn;
        $arr = $this->test_arr;
        $arr = array_combine($this->headers, $arr);
        $csv = new BigCsv($csv_fn);
        $csv->writeRowToCsv($arr, $this->headers);
        $lines = file($csv_fn);
        $expected = $arr;
        //$actual   = array_combine(str_getcsv($lines[0]), str_getcsv($lines[1]));
        $this->assertEquals($expected, file($csv_fn));
    }
    public function testUpdateRowInCsvReturnsTrueIfUpdateOk()
    {
        $csv_fn = $this->csvTestFn;
        $arr = $this->test_arr;
        $arr = array_combine($this->headers, $arr);
        $csv = new BigCsv($csv_fn);
        $csv->writeRowToCsv($arr, $this->headers);
        $csv->writeRowToCsv($arr, $this->headers);
        $search = 'Barney Rubble';
        $replace = ['web_person_email' => 'pebbles@flintstone.com','web_person_name' => 'Pebbles Flintstone'];
        $expected = TRUE;
        $actual   = $csv->updateRowInCsv($search, $replace, $this->headers, FALSE);
        $this->assertEquals($expected, $actual);
    }
    public function testUpdateRowInCsvChangedFieldsAreUpdatedOk()
    {
        $csv_fn = $this->csvTestFn;
        $arr = $this->test_arr;
        $arr = array_combine($this->headers, $arr);
        $csv = new BigCsv($csv_fn);
        $csv->writeRowToCsv($arr, $this->headers);
        $search = 'Barney Rubble';
        $replace = ['web_person_email' => 'pebbles@flintstone.com','web_person_name' => 'Pebbles Flintstone'];
        $csv->updateRowInCsv($search, $replace, $this->headers, FALSE);
        $lines = file($csv_fn);
        $row   = array_combine(str_getcsv($lines[0]), str_getcsv($lines[1]));
        $expected = 'pebbles@flintstone.com';
        $actual   = $row['web_person_email'] ?? 'XXX';
        $this->assertEquals($expected, $actual);
    }
    /*
    public function testUpdateRowInCsvNonChangedFieldsAreLeftAlone()
    {
        $csv_fn = $this->csvTestFn;
        $arr = $this->test_arr;
        $arr = array_combine($this->headers, $arr);
        $csv = new BigCsv($csv_fn);
        $csv->writeRowToCsv($arr, $this->headers);
        $csv->writeRowToCsv($arr, $this->headers);
        $search = 'Barney Rubble';
        $replace = ['web_person_email' => 'pebbles@flintstone.com','web_person_name' => 'Pebbles Flintstone'];
        $csv->updateRowInCsv($search, $replace, $this->headers, FALSE);
        $lines = file($csv_fn);
        $row   = array_combine(str_getcsv($lines[0]), str_getcsv($lines[2]));
        $expected = 'silver';
        $actual   = $row['add_on_plan'] ?? 'XXX';
        $this->assertEquals($expected, $actual);
    }
    public function testDeleteRowInCsv()
    {
        $email = 'barney@flintstone.com';
        $csv_fn = $this->csvTestFn;
        $arr = $this->test_arr;
        $arr = array_combine($this->headers, $arr);
        $csv = new BigCsv($csv_fn);
        $csv->writeRowToCsv($arr, $this->headers);
        $arr['web_person_email'] = $email;
        $csv->writeRowToCsv($arr, $this->headers);
        $csv->deleteRowInCsv($email, $this->headers);
        $file = file($csv_fn);
        $expected = 2;
        $actual   = count($file);
        $this->assertEquals($expected, $actual, 'Incorrect number of rows in CSV');
    }
    public function testDeleteRowInCsvDoesNotOverwriteIfFlagNotSet()
    {
        $email = 'barney@flintstone.com';
        $csv_fn = $this->csvTestFn;
        $arr = $this->test_arr;
        $arr = array_combine($this->headers, $arr);
        $csv = new BigCsv($csv_fn);
        $csv->writeRowToCsv($arr, $this->headers);
        $arr['web_person_email'] = $email;
        $csv->writeRowToCsv($arr, $this->headers);
        $csv->deleteRowInCsv($email, $this->headers, FALSE, FALSE);
        $file = file($csv_fn);
        $expected = 3;
        $actual   = count($file);
        $this->assertEquals($expected, $actual, 'Incorrect number of rows in CSV');
    }
    public function testWriteRowToCsvReturnsExpectedRowsIfHeadersNotUsed()
    {
        $csv_fn = $this->csvTestFn;
        copy($this->csvFn, $csv_fn);
        $arr = $this->test_arr;
        $csv = new BigCsv($csv_fn);
        $expected = count(file($csv_fn)) + 1;
        $csv->writeRowToCsv($arr);
        $actual = count(file($csv_fn));
        $this->assertEquals($expected, $actual);
    }
    public function testDeleteRowInCsvRetainsTmpFileIfFlagSet()
    {
        $email = 'barney@flintstone.com';
        $csv_fn = $this->csvTestFn;
        $arr = $this->test_arr;
        $arr = array_combine($this->headers, $arr);
        $csv = new BigCsv($csv_fn);
        $csv->writeRowToCsv($arr, $this->headers);
        $arr['web_person_email'] = $email;
        $csv->writeRowToCsv($arr, $this->headers);
        $csv->deleteRowInCsv($email, $this->headers, FALSE, TRUE, $this->tmp_fn, FALSE);
        $expected = '';
        $actual   = file_get_contents($this->tmp_fn);
        $this->assertEquals($expected, $actual);
    }
    public function testDeleteRowInCsvOverwritesCsvFileIfFlagSet()
    {
        $email = 'barney@flintstone.com';
        $csv_fn = $this->csvTestFn;
        $arr = $this->test_arr;
        $arr = array_combine($this->headers, $arr);
        $csv = new BigCsv($csv_fn);
        $csv->writeRowToCsv($arr, $this->headers);
        $arr['web_person_email'] = $email;
        $csv->writeRowToCsv($arr, $this->headers);
        $csv->deleteRowInCsv($email, $this->headers, FALSE, TRUE, $this->tmp_fn, FALSE);
        $expected = file_get_contents($csv_fn);
        $actual   = file_get_contents($this->tmp_fn);
        $this->assertEquals($expected, $actual);
    }
    public function testDeleteRowInCsvDoesNotOverwritesCsvFileIfFlagSetFalse()
    {
        $email = 'barney@flintstone.com';
        $csv_fn = $this->csvTestFn;
        $arr = $this->test_arr;
        $arr = array_combine($this->headers, $arr);
        $csv = new BigCsv($csv_fn);
        $csv->writeRowToCsv($arr, $this->headers);
        $arr['web_person_email'] = $email;
        $csv->writeRowToCsv($arr, $this->headers);
        $csv->deleteRowInCsv($email, $this->headers, FALSE, FALSE, $this->tmp_fn, FALSE);
        $expected = file_get_contents($csv_fn);
        $actual   = file_get_contents($this->tmp_fn);
        $this->assertEquals($expected, $actual);
    }
    */
}
