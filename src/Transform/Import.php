<?php
namespace SimpleHtml\Transform;

/*
 * Unlikely\Import\Transform\Replace
 *
 * @description performs search and replace using str_replace() or str_ireplace()
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
class Import
{
    const DEFAULT_START = '<body>';
    const DEFAULT_STOP  = '</body>';
    /**
     * Grabs contents, applies transforms
     *
     * @string $url         : source URL
     * @array  $callbacks   : array of transform callbacks; expects key "callback"
     * @string $delim_start : where to start contents extraction
     * @string $delim_stop  : where to end contents extraction
     * @return string $html : transformed HTML
     */
    public static function import(string $url,
                                  array $callbacks = [],
                                  string $delim_start = self::DEFAULT_START,
                                  string $delim_stop = self::DEFAULT_STOP)
    {
        $html = file_get_contents($url);
        $html = self::extract($html, $delim_start, $delim_stop);
        if (!empty($html) && !empty($callbacks)) {
            foreach ($callbacks as $item) {
                $obj    = $item['callback'] ?? FALSE;
                $params = $item['params'] ?? [];
                $html   = (!empty($obj)) ? $obj($html, $params) : $html;
            }
        }
        return $html;
    }
    /**
     * Grabs contents from between start/stop delimiters
     *
     * @string $contents    : HTML contents
     * string $delim_start  : where to start extraction
     * string $delim_stop   : where to end extraction; if NULL, you get contents starting with $delim_start and onwards
     * bool $case_sensitive : set TRUE if you want case sensitive
     * @return string $html : transformed HTML
     */
    public static function get_delimited(string $contents, string $delim_start, string $delim_stop = '', bool $case_sensitive = FALSE)
    {
        $max   = strlen($contents);
        $start = NULL;
        $stop  = NULL;
        $html  = $contents;
        if ($case_sensitive) {
            $start = strpos($contents, $delim_start);
        } else {
            $start = stripos($contents, $delim_start);
        }
        // if start delim not found, just return the contents
        if ($start === FALSE) return $contents;
        // validate start/stop
        $start = $start + strlen($delim_start);
        if ($start >= $max) return $contents;
        if ($delim_stop === '') {
            $html = substr($contents, $start);
        } else {
            if ($case_sensitive) {
                $stop = strpos($contents, $delim_stop, $start);
            } else {
                $stop = stripos($contents, $delim_stop, $start);
            }
            $length = $start - $stop;
            if (!empty($length) && ($start + $length) <= $max) {
                $html = substr($contents, $start, $length);
            }
        }
        return $html;
    }
}
