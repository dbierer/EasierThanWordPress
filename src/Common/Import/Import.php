<?php
namespace FileCMS\Common\Import;

/*
 * FileCMS\Common\Import\Import
 *
 * @description imports contents from "trusted" website(s)
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
use Throwable;
use FileCMS\Common\Page\Edit;
use FileCMS\Common\Generic\Messages;
use FileCMS\Common\Transform\Transform;
class Import
{
    const DEFAULT_START = '<body>';
    const DEFAULT_STOP  = '</body>';
    const ERROR_UPLOAD  = 'ERROR: unable to upload list of URLs to import';
    const ERROR_URL_EMPTY = 'ERROR: no data returned from this URL. Check HTTP status on this URL.';
    const URLS_KEY   = 'URLS';
    const CONFIG_KEY = 'IMPORT';
    public static $list = [];
    public static $config = [];
    /**
     * Grabs contents, applies transforms
     * If $delim_stop is an array:
     * 1. Splits text with the last occurance of the first item in the array
     * 2. Splits remaining text with the last occurance of the next item in the array
     * 3. Keeps going until all $delim_stop markers have been processed
     *
     * @param string $url         : source URL
     * @param array  $callbacks   : array of transform callbacks; expects key "callback"
     * @param string $delim_start : where to start contents extraction
     * @param string|array $delim_stop  : where to end contents extraction
     * @return string $html : transformed HTML or ''
     */
    public static function import(string $url,
                                  array $callbacks = [],
                                  string $delim_start = self::DEFAULT_START,
                                  $delim_stop = self::DEFAULT_STOP)
    {
        // make sure URL is reachable
        $html = '';
        try {
            $url = trim($url);
            $html = file_get_contents("$url");
            if (empty($html)) {
                $html = '';
            } else {
                $html = self::get_delimited($html, $delim_start, $delim_stop);
                $html = Transform::transform($html, $callbacks);
            }
        } catch (Throwable $t) {
            error_log(__METHOD__ . ':' . $t->getMessage());
        }
        return $html;
    }
    /**
     * Grabs contents from between start/stop delimiters
     *
     * @string $contents    : HTML contents
     * string $delim_start  : where to start extraction
     * string $delim_stop   : where to end extraction; if NULL, you get contents starting with $delim_start and onwards
     * @return string $html : transformed HTML
     */
    public static function get_delimited(string $contents,
                                         string $delim_start,
                                         $delim_stop = NULL)
    {
        $html  = $contents;
        $start = strpos($contents, $delim_start);
        // if start delim not found, just return the contents
        if ($start === FALSE) return $contents;
        $temp = explode($delim_start, $contents);
        if (!empty($temp[1])) {
            $html = $temp[1];
            if (!empty($delim_stop)) {
                if (is_string($delim_stop)) {
                    $stop = strpos($html, $delim_stop);
                    if ($stop !== FALSE) {
                        $again = explode($delim_stop, $html);
                        $html  = $again[0] ?? $html;
                    }
                } elseif (is_array($delim_stop)) {
                    foreach ($delim_stop as $marker) {
                        $stop = strpos($html, $marker);
                        if ($stop !== FALSE) {
                            $again = explode($marker, $html);
                            if (count($again) > 1) array_pop($again);
                            $html  = implode('', $again);
                        }
                    }
                }
            }
        }
        return trim($html);
    }
    /**
     * Checks to see if URL is on "trusted" list
     *
     * @param string $url : URL to test
     * @param array $trusted : array of "trusted" URL prefixes
     * @return bool TRUE if trusted; FALSE otherwise
     */
    public static function is_trusted(string $url, array $trusted) : bool
    {
        $ok = 0;
        foreach ($trusted as $item) {
            if (strpos($url, $item) === 0) {
                $ok++;
                break;
            }
        }
        return (bool) $ok;
    }
    /**
     * Performs actual import
     *
     * @param string $url
     * @param array $trusted : prefixes of URLs from which to allow import
     * @param array $transform : tranformation filter rules
     * @param string $delim_start : starting delimiter
     * @param string|array $delim_stop : ending delimiter(s)
     * @param Edit $edit : used to save
     * @param Messages $message
     * @param string $backup_dir : backup directory
     * @param string $path : where to save files; default === HTML_DIR
     * @param bool $tidy : set TRUE to use Tidy extension to cleanup upon save
     * @return boolean TRUE if OK; FALSE otherwise
     */
    public static function do_import(string $url,
                       array $trusted,
                       array $transform,
                       string $delim_start,
                       $delim_stop,
                       Edit $edit,
                       Messages $message,
                       string $backup_dir,
                       string $path = HTML_DIR,
                       bool $tidy = TRUE)
    {
        if (!Import::is_trusted($url, $trusted)) return FALSE;
        set_time_limit(30);
        $ok   = FALSE;
        $html = self::import($url, $transform, $delim_start, $delim_stop);
        if (empty($html)) {
            $message->addMessage(Import::ERROR_URL_EMPTY);
        } else {
            $key  = $edit->getKeyFromURL($url, $path);
            if ($edit->save($key, $html, $backup_dir, $path, $tidy)) {
                $message->addMessage(Edit::SUCCESS_SAVE . ' ' . $key);
                $ok = TRUE;
            } else {
                $message->addMessage(Edit::ERROR_SAVE . ' ' . $key);
            }
        }
        return ($ok) ? $key : FALSE;
    }
    /**
     * Process bulk imports
     *
     * @param array $list : list of URLs to be imported
     * @param array $trusted : prefixes of URLs from which to allow import
     * @param array $transform : tranformation filter rules
     * @param string $delim_start : starting delimiter
     * @param string|array $delim_stop : ending delimiter(s)
     * @param Edit $edit : used to save
     * @param Messages $message
     * @param string $backup_dir : backup directory
     * @param string $path : where to save files; default === HTML_DIR
     * @param bool $tidy : set TRUE to use Tidy extension to cleanup upon save
     * @return array $bulk : list of URLs that were imported: 0 => [failed], 1 => [succeeded]
     */
    public static function do_bulk_import(
        array $list,
        array $trusted,
        array $transform,
        string $delim_start,
        $delim_stop,
        Edit $edit,
        Messages $message,
        string $backup_dir,
        string $path = HTML_DIR,
        bool $tidy = TRUE)
    {
        $bulk = [];
        foreach ($list as $url) {
            $url  = strip_tags(trim($url));
            $key = self::do_import($url, $trusted, $transform, $delim_start, $delim_stop, $edit, $message, $backup_dir, $path, $tidy);
            if ($key === FALSE) {
                $bulk[0][] = $key;
            } else {
                $bulk[1][] = $key;
            }
        }
        return $bulk;
    }
}
