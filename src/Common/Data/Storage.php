<?php
namespace FileCMS\Common\Data;
/*
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
 * Stores information in designated file
 */
use SplFileObject;
use Closure;
use Throwable;
use RuntimeException;
use FileCMS\Common\Data\Csv\Csv;
use FileCMS\Common\Data\Json\Json;
use FileCMS\Common\Data\Php\Native;
class Storage
{
    const DEFAULT_FN  = 'contacts.txt';
    const DEFAULT_DIR = BASE_DIR . '/data';
    const DEFAULT_FMT = 'csv';
    const FMT_CSV     = 'csv';
    const FMT_PHP     = 'php';
    const FMT_JSON    = 'json';
    const ERR_UNABLE  = 'ERROR: unable to access storage. Permissions issue?';
    public $fn       = '';
    public $format   = 'php';    // "json" otherwise
    public $strategy = NULL;
    public $config   = [];
    /**
     * @param array $config : /src/config/config.php => 'STORAGE'
     */
    public function __construct(array $config)
    {
        $this->config = $config['STORAGE'] ?? [];
        $storage_fn   = $this->config['storage_fn']  ?? self::DEFAULT_FN;
        $storage_dir  = $this->config['storage_dir'] ?? self::DEFAULT_DIR;
        $format       = $this->config['storage_fmt'] ?? self::DEFAULT_FMT;
        $this->setFormat($format);
        try {
            if (!file_exists($storage_dir)) {
                mkdir($storage_dir, 0775, TRUE);
            }
            $this->fn = str_replace('//', '/', $storage_dir . '/' . $storage_fn);
            touch($this->fn);
        } catch (Throwable $t) {
            error_log(__METHOD__ . ':' . $t->getMessage());
            throw new RuntimeException(self::ERR_UNABLE);
        }
    }
    /**
     * Sets format strategy
     * save + fetch methods set as as Closure instances
     * to expand strategies: create class that implements Common\Data\FormatStrategyInterface
     * NOTE: if using PHP 8.1 an Enum could be used
     *
     * @param string $fmt : csv|php|json
     * @return string $fmt : csv|php|json
     */
    public function setFormat(string $fmt)
    {
        switch ($fmt) {
            case self::FMT_PHP :
                $val = self::FMT_PHP;
                $this->strategy = 'Native';
                break;
            case self::FMT_JSON :
                $val = self::FMT_JSON;
                $this->strategy = 'Json';
                break;
            case self::FMT_CSV :
            default :
                $val = self::FMT_CSV;
                $this->strategy = 'Csv';
                break;
        }
    }
    /**
     * Stores info into storage using Closure
     *
     * @param string $fn   : filename
     * @param array $data  : data to be stored; forces $data to type "array"
     * @param bool $append : if TRUE, append to existing storage, otherwise overwrite
     * @return bool
     */
    public static function save(string $fn, $data, bool $append = TRUE)
    {
        return (bool) $this->$strategy::save($fn, $data, $append);
    }
    /**
     * Retrieves info from storage using fgetcsv()
     *
     * @param string $fn   : filename
     * @param bool $array  : if TRUE, returns data as array
     * @param bool $erase  : if TRUE, erase existing storage after retrieval
     * @return array $data : array of mixed stored data
     */
    public static function fetch(string $fn, bool $array = TRUE, bool $erase = FALSE)
    {
        $result = $this->$strategy::fetch($fn, $array, $erase);
        if ($erase) file_put_contents($fn, '');
        return $result;
    }
}
