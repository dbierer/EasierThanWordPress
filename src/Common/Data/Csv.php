<?php
namespace FileCMS\Common\Data;
/*
 * Treats CSV file like database
 * Lets you search, update and delete rows
 * IMPORTANT: uses file() function which means the entire CSV file will be in memory
 * IMPORTANT: ***cannot*** use this for large CSV files > 50 M in size
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are
 * met:
 *
 * * Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above
 *   copyright notice, this list of conditions and the following disclaimer
 *   in the documentation and/or other materials provided with the
 *   distribution.
 * * Neither the name of the  nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */
/**
 * @todo Create a large file version of this
 */
use Throwable;
use Exception;
use SplFileObject;
use FileCMS\Common\Contact\Email;
use FileCMS\Common\Generic\Messages;
class Csv
{
    const ERR_CSV   = 'ERROR: CSV file error';
    public $pos     = FALSE;
    public $lines   = [];
    public $headers = [];
    public $csv_fn  = '';
    /**
     * If CSV file doesn't exist, creates the file
     * If $headers aren't empty, also writes out headers
     *
     * @param string $csv_fn : filename of CSV file
     * @param array $headers : optional array of headers
     * @return void
     */
    public function __construct(string $csv_fn, array $headers = [])
    {
        $this->csv_fn = $csv_fn;
        $this->headers = $headers;
        if (!file_exists($csv_fn)) {
            if (empty($headers)) {
                touch($csv_fn);
            } else {
                $obj = new SplFileObject($this->csv_fn, 'w');
                $obj->fputcsv($headers);
                unset($obj);
            }
        } else {
            $this->lines  = file($csv_fn, FILE_SKIP_EMPTY_LINES);
        }
    }
    /**
     * Gets current size of $this->csv_fn
     *
     * @return int $size
     */
    public function getSize()
    {
        return count($this->lines);
    }
    /**
     * Gets list of items from CSV
     *
     * @param string|array $key_field : header(s) to use as key; leave blank for numeric array
     * @param bool $first_row  : TRUE : 1st row contains headers; FALSE : no headers
     * @return array $select : [key => value]; key === practice_key; value = $row
     */
    public function getItemsFromCsv($key_field = NULL, bool $first_row = TRUE) : array
    {
        $obj     = new SplFileObject($this->csv_fn, 'r');
        $select  = [];
        $headers = [];
        $count   = 0;
        $def_key = date('Ymd');
        $idx     = 0;
        while ($row = $obj->fgetcsv()) {
            if (empty($row) || count($row) <= 1) continue;
            // if $key_fiels is NULL, just append $row
            if (empty($key_field)) {
                $select[] = $row;
                continue;
            }
            // draw headers from first line
            if (empty($headers) && $first_row) {
                $headers = $row;
                $this->headers = $headers;
            } else {
                $abs = count($row) - count($headers);
                $abs = ($abs >= 0) ? $abs : -$abs;
                switch (count($headers) <=> count($row)) {
                    case -1 :
                        for ($x = 1; $x <= $abs; $x++)
                            $headers[] = sprintf('Header_%02d', $x);
                        break;
                    case 1 :
                        for ($x = 1; $x <= $abs; $x++)
                            array_pop($headers);
                        break;
                    case 0 :
                    default :
                        // do nothing
                }
                $data = array_combine($headers, $row);
                // build key
                $key  = '';
                if (is_array($key_field)) {
                    foreach ($key_field as $name) {
                        if (!empty($data[$name])) {
                            $key .= trim($data[$name]) . '_';
                        } else {
                            $key .= $def_key . sprintf('%4d_',$idx++);
                        }
                    }
                    $key = substr($key, 0, -1);
                } else {
                    $key = trim($data[$key_field]);
                }
                $select[$key] = $data;
            }
        }
        ksort($select);
        return $select;
    }
    /**
     * Writes row to CSV
     *
     * @param array $post       : normally sanitized $_POST
     * @param array $csv_fields : array of CSV headers; leave blank if headers not used
     * @return bool             : TRUE if entry made OK
     */
    public function writeRowToCsv(array $post, array $csv_fields = []) : bool
    {
        $ok = FALSE;
        try {
            $obj = new SplFileObject($this->csv_fn, 'a');
            if (empty($csv_fields)) {
                $data = $post;
            } else {
                // write headers if filesize is 0
                if ($this->getSize() === 0) $obj->fputcsv($csv_fields);
                // align $_POST data to csv fields
                $data = [];
                foreach ($csv_fields as $name)
                    $data[$name] = $post[$name] ?? '';
            }
            $ok = (bool) $obj->fputcsv(array_values($data));
        } catch (Throwable $t) {
            error_log(__METHOD__ . ':' . get_class($t) . ':' . $t->getMessage() . ':' . $t->getTraceAsString());
        }
        unset($obj);
        $this->lines = file($this->csv_fn, FILE_SKIP_EMPTY_LINES);
        return $ok;
    }
    /**
     * Finds key in CSV file
     * Assumes first row is headers unless $first === FALSE
     * Stores contents of CSV file in $this->lines
     * If found, sets $this->pos to the line number of the row found in $this->lines
     *
     * @param string $search  : any value that might be in the CSV file
     * @param bool $case      : TRUE: case sensitive; FALSE: [default] case insensitive search
     * @param bool $first_row : TRUE [default]: first row is headers; FALSE: first row is data
     * @return array
     */
    public function findItemInCSV(string $search, bool $case = FALSE, bool $first = TRUE) : array
    {
        // otherwise process as normal
        $func  = ($case) ? 'strpos' : 'stripos';
        $found = [];
        $hdr_count = 0;
        $this->pos = 0;
        $this->headers = [];
        $this->lines   = file($this->csv_fn, FILE_SKIP_EMPTY_LINES);
        foreach($this->lines as $key => $row) {
            if ($first && empty($this->headers)) {
                $this->headers = str_getcsv($row);
                $hdr_count = count($this->headers);
            } else {
                if ($func($row, $search) !== FALSE) {
                    $found = str_getcsv($row);
                    $this->pos = $key;
                    if ($first && $hdr_count === count($found)) {
                        $found = array_combine($this->headers, $found);
                    }
                    break;
                }
            }
        }
        return $found;
    }
    /**
     * Deletes row in CSV file
     * If you don't supply $csv_fields, assumes no headers
     * If $overwrite is set TRUE (default), CSV is rewritten minus deleted row
     * If $overwrite is set FALSE, CSV::$lines reflects deletion, but CSV file itself remains the same
     *
     * @param string $search    : any value that might be in the CSV file
     * @param array $csv_fields : array of fields names; leave blank if you don't use headers
     * @param bool $case        : TRUE: case sensitive; FALSE: [default] case insensitive search
     * @param bool $overwite    : TRUE: write contents (minus deleted row) back to CSV
     * @return bool             : TRUE if entry deleted OK
     */
    public function deleteRowInCsv(string $search, array $csv_fields = [], bool $case = FALSE, bool $overwrite = TRUE) : bool
    {
        $row = $this->findItemInCSV($search, $case, (!empty($csv_fields)));
        if (empty($row)) return FALSE;
        if (!empty($csv_fields)) {
            foreach ($row as $key => $value)
                if (!empty($data[$key])) $row[$key] = $data[$key];
        }
        // remove row
        unset($this->lines[$this->pos]);
        // write CSV back out if $overwrite flag is set
        return (!$overwrite) ? TRUE : (bool) file_put_contents($this->csv_fn, $this->lines);
    }
    /**
     * Updates row in CSV file
     * If you don't supply $csv_fields, assumes no headers
     * If no headers, update does delete and then insert
     *
     * @param string $search  : any value that might be in the CSV file
     * @param array $data     : array of items to update
     * @param array $csv_fields : array of fields names; leave blank if you don't use headers
     * @param bool $case      : TRUE: case sensitive; FALSE: [default] case insensitive search
     * @return bool             : TRUE if entry made OK
     */
    public function updateRowInCsv(string $search, array $data, array $csv_fields = [], bool $case = FALSE) : bool
    {
        $row = $this->findItemInCSV($search, $case, (!empty($csv_fields)));
        if (!$this->deleteRowInCsv($search, $csv_fields, $case, FALSE)) return FALSE;
        // update $row with $data
        foreach ($row as $key => $value)
            if (!empty($data[$key])) $row[$key] = $data[$key];
        // append row to $lines
        $this->lines[] = static::array2csv(array_values($row)) . PHP_EOL;
        // write CSV back out
        return (bool) file_put_contents($this->csv_fn, $this->lines);
    }
    /**
     * This writes an array to CSV
     * Credits: https://stackoverflow.com/questions/13108157/php-array-to-csv
     *
     * @param array $data : data to be written
     * @return string $csv_string
     */
    public static function array2csv(array $data) : string
    {
        $f = fopen('php://memory', 'w+');
        fputcsv($f, $data);
        rewind($f);
        $str = trim(stream_get_contents($f));
        fclose($f);
        return $str;
    }
}
