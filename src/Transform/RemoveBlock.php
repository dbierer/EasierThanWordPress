<?php
namespace FileCMS\Transform;

/*
 * FileCMS\Transform\Clean
 *
 * @description Removes blocks based up search criteria, start and stop strings
 *
 * @author doug@unlikelysource.com
 * @date 2021-10-04
 * Copyright 2021 unlikelysource.com
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
use InvalidArgumentException;
use FileCMS\Common\Transform\Base;
class RemoveBlock extends Base
{
    const DESCRIPTION = 'Remove blocks based up search criteria, start and stop strings';
    const ERR_PARAMS = 'ERROR: parameter array must contain the keys "start", "stop" and "items"';
    public $start = NULL;  // starting string
    public $stop  = NULL;  // ending string
    public $items = [];    // array of search items used to confirm block to be removed
    protected $beg_pos = NULL;  // start pos of block to be removed
    protected $end_pos = NULL;  // end pos of block to be removed
    /**
     * Removes blocks based up search criteria, start and stop strings
     *
     * @param string $html : HTML string to be cleaned
     * @param array $params : ['start' => : starting string for block to be removed,
     *                         'stop'  => : ending string for block to be removed; must occur *after* start string
     *                         'items' => : array of strings that occur between "start" and "stop", used to correctly identify block to be removed
     * @return string $html : HTML with identified block removed
     */
    public function __invoke(string $html, array $params = []) : string
    {
        $this->init($params);
        if ($this->getStartAndStop($html)) {
            if ($this->confirm($html, $this->items)) {
                $html = $this->remove($html);
            }
        }
        return $html;
    }
    /**
     * Initializes properties
     *
     * @param array $params : ['start' => : starting string for block to be removed,
     *                         'stop'  => : ending string for block to be removed; must occur *after* start string
     *                         'items' => : array of strings that occur between "start" and "stop", used to correctly identify block to be removed
     * @return void
     * @throws InvalidArgumentException
     */
    public function init(array $params) : void
    {
        $this->start = $params['start'] ?? '';
        $this->stop  = $params['stop']  ?? '';
        $this->items = $params['items'] ?? [];
        if (empty($this->start) || empty($this->stop) || empty($this->items))
            throw new InvalidArgumentException(self::ERR_PARAMS);
    }
    /**
     * Populates $this->beg_pos and $this->end_pos
     *
     * @param string $contents : document to be searched
     * @return bool TRUE if both contain beg_pos and end_pos values, and beg_pos < end_pos; FALSE otherwise
     */
    public function getStartAndStop(string $contents)
    {
        $this->beg_pos = strpos($contents, $this->start);
        $this->end_pos = strpos($contents, $this->stop);
        $valid = 4;
        $found = 0;
        $found += (int) (is_int($this->beg_pos));
        $found += (int) (is_int($this->end_pos));
        $found += (int) (((int) $this->end_pos) < strlen($contents));
        $found += (int) ($this->beg_pos < $this->end_pos);
        return ($found === $valid);
    }
    /**
     * Confirms that all items in $search exist between $this->start and $this->stop
     *
     * @param string $contents : document to be searched
     * @return bool TRUE if all items found; FALSE otherwise
     */
    public function confirm(string $contents)
    {
        $max = count($this->items);
        $found = 0;
        foreach ($this->items as $needle) {
            $pos = strpos($contents, $needle);
            if ($pos !== FALSE
                && $pos > $this->beg_pos
                && $pos < $this->end_pos) { $found++; }
        }
        return ($found === $max);
    }
    /**
     * Removes block between $this->beg_pos and $this->end_pos
     *
     * @return string $contents : HTML with block removed
     */
    public function remove(string $contents)
    {
        $begin = $this->beg_pos;
        $end   = $this->end_pos + strlen($this->stop);
        $first = substr($contents, 0, $begin);
        $last  = substr($contents, $end);
        $contents = $first . $last;
        return $contents;
    }
    /**
     * Returns all object vars
     *
     * @return array : key/value pairs, all object variables
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}
