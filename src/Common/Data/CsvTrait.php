<?php
namespace FileCMS\Common\Data;
/*
 * Contains array methods that expand upon array_combine() and go from array to CSV
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
use SplFileObject;
use ArrayIterator;
trait CsvTrait
{
    const HDR_PREFIX = 'header_%02d';
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
    /**
     * Does array_combine() for unequal header count
     * NOTE: if 2nd arg is an associative array, headers will get stripped
     *
	 * @todo Move `array_combine_whatever()` to a generic class w/ static usage
	 * @todo Set up `CsvTrait::array_combine_whatever()` to make a static call to this generic class
     * @param array $headers : desired headers
     * @param array $data    : numberic array of data to be combined with headers
     * @return array $combined : associative array
     */
    public function array_combine_whatever(array $headers, array $data) : array
    {
        $combined = [];
        if (count($headers) === count($data)) {
            $combined = array_combine($headers, $data);
        } else {
            $iter = new ArrayIterator(array_values($data));
            if (count($headers) < $iter->count()) {
                foreach ($headers as $key) {
                    $combined[$key] = $iter->current();
                    $iter->next();
                }
                $pos = 1;
                while ($iter->valid()) {
                    $key = sprintf(static::HDR_PREFIX, $pos++);
                    $combined[$key] = $iter->current();
                    $iter->next();
                }
            } else {
                foreach ($iter as $value) {
                    $combined[current($headers)] = $value;
                    next($headers);
                }
            }
        }
        return $combined;
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
            // if $key_fields is NULL, just append $row
            if (empty($key_field)) {
                $select[] = $row;
                continue;
            }
            // draw headers from first line
            if (empty($headers) && $first_row) {
                $headers = $row;
                $this->headers = $headers;
            } else {
                $data = $this->array_combine_whatever($headers, $row);
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
}
