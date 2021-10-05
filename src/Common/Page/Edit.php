<?php
namespace SimpleHtml\Common\Page;
/*
 * Author: doug@unlikelysource.com
 * License: BSD
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
use SplFileInfo;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilterIterator;
class Edit
{
    const DEFAULT_EXT  = ['html', 'htm'];
    const CHOOSE_TOP   = '--choose--';
    const SUCCESS_SAVE = 'SUCCESS: page saved successfully';
    const SUCCESS_DEL  = 'SUCCESS: page deleted successfully';
    const SUCCESS_CANCEL = 'Operation cancelled';
    const ERROR_SAVE   = 'ERROR: unable to save page';
    const ERROR_DEL    = 'ERROR: unable to delete page';
    const ERROR_KEY    = 'ERROR: missing, unknown or invalid URL';
    public $allowed = [];   // allowed extensions
    public $config  = [];
    public $pages   = [];
    /**
     * Stores configuration
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $allowed = $config['SUPER']['allowed_ext'] ?? self::DEFAULT_EXT;
        if (!defined('EDIT_ALLOWED_EXT'))
            define('EDIT_ALLOWED_EXT', $allowed);
        $this->allowed = $allowed;
    }
    /**
     * Returns key from filename
     *
     * @param string $fn   : filename
     * @param string $path : usually HTML_DIR
     * @return $key        : sanitized filename
     */
    public function getKeyFromFilename(string $fn, string $path)
    {
        $key = str_replace($path, '', $fn);
        if ($key[0] !== '/') $key = '/' . $key;
        foreach ($this->allowed as $ext)
            $key = str_ireplace('.' . $ext, '', $key);
        return trim($key);
    }
    /**
     * Returns list of pages from starting point HTML_DIR
     * Note: HTML_DIR is a global constant defined in /public/index.php
     *
     * @param string $path  : starting path (if other than HTML_DIR
     * @return array $pages : [URL key => full path, URL key => full path]
     */
    public function getListOfPages(string $path = HTML_DIR)
    {
        if (empty($this->pages)) {
            $iter = new RecursiveDirectoryIterator($path);
            $itIt = new RecursiveIteratorIterator($iter);
            $filt = new class ($itIt, $this->allowed) extends FilterIterator {
                public $allowed = [];
                public function __construct($iter, $allowed)
                {
                    parent::__construct($iter);
                    $this->allowed = $allowed;
                }
                public function accept()
                {
                    $ok  = 0;
                    $obj = $this->current() ?? FALSE;
                    if ($obj && $obj instanceof SplFileInfo) {
                        $ext = strtolower($obj->getExtension());
                        foreach ($this->allowed as $allowed) {
                            $ok += (int) ($ext === $allowed);
                        }
                    }
                    return (bool) $ok;
                }
            };
            foreach ($filt as $name => $obj)
                $this->pages[$this->getKeyFromFilename($name, $path)] = $name;
            ksort($this->pages);
        }
        return $this->pages;
    }
    /**
     * Strips path from URL and returns page contents
     * If page doesn't exist, returns empty string
     *
     * @param string $url       : URL used to view page
     * @param string $path      : starting path (if other than HTML_DIR)
     * @return string $contents : HTML contents of page
     */
    public function getPageFromURL(string $url, string $path = HTML_DIR) : string
    {
        $key  = $this->getKeyFromURL($url);
        return $this->getContentsFromPage($key, $path);
    }
    /**
     * Builds a page key from a URL
     * If page doesn't exist, returns empty string
     *
     * @param string $url   : URL used to view page
     * @param string $path      : starting path (if other than HTML_DIR)
     * @return string $key  : key in $this->pages
     */
    public function getKeyFromURL(string $url, string $path = HTML_DIR) : string
    {
        $key = parse_url($url, PHP_URL_PATH) ?? '';
        // get rid of extension (if any)
        if (strpos($key, '.') !== FALSE) {
            $temp = explode('.', $key);
            $key  = $temp[0] ?? $key;
        }
        if (!empty($key))
            if ($key[0] !== '/') $key = '/' . $key;
        return trim($key);
    }
    /**
     * Returns contents of file listed in $this->pages
     * If page doesn't exist, returns empty string
     *
     * @param string $key       : key in $this->pages
     * @param string $path      : starting path (if other than HTML_DIR
     * @return string $contents : HTML contents of page
     */
    public function getContentsFromPage(string $key, string $path = HTML_DIR) : string
    {
        $html = '';
        $pages = $this->getListOfPages($path);
        if (!empty($pages[$key]))
            if (file_exists($pages[$key]))
                $html = file_get_contents($pages[$key]);
            return $html;
    }
    /**
     * Saves new or revised page
     *
     * @param string $key       : URI path
     * @param string $contents  : HTML
     * @param string $path      : starting path (if other than HTML_DIR)
     * @param bool   $tidy      : If set TRUE, fixes using Tidy extension
     * @return bool TRUE if save was successful; FALSE otherwise
     */
    public function save(string $key, string $contents, string $path = HTML_DIR, bool $tidy = FALSE) : bool
    {
        // use Tidy to sanitize
        $ok = 0;
        if (function_exists('tidy_repair_string') && $tidy) {
            $fixed = tidy_repair_string($contents);
            // extract content between <body>*</body> tags
            $matches = [];
            [$first, $last] = explode('<body>', $fixed);
            $pos = strpos($last, '</body>');
            $contents = substr($last, 0, $pos);
        }
        // check to see if it's an existing file
        $pages = $this->getListOfPages($path);
        $fn    = $pages[$key] ?? '';
        // if we've got a filename from $this->pages, overwrite it
        if (!empty($fn)) {
            $ok = file_put_contents($fn, $contents);
        // if key doesn't exist, it's a new file
        } else {
            // split key to get directory and filename
            $parts = explode('/', $key);
            $fn    = array_pop($parts);
            $dir   = '/' . implode('/', $parts);
            $dir   = $path . '/' . $dir;
            $dir   = str_replace('//', '/', $dir);
            $fn    = $dir . '/' . $fn . '.html';
            $fn    = str_replace('//', '/', $fn);
            // create directory if needed
            if (!file_exists($dir)) mkdir($dir, 0755, TRUE);
            // save to file
            $ok = file_put_contents($fn, $contents);
        }
        return (bool) $ok;
    }
    /**
     * Deletes page
     *
     * @param string $key : URI path
     * @param string $path      : starting path (if other than HTML_DIR)
     * @return bool TRUE if delete was successful; FALSE otherwise
     */
    public function delete(string $key, string $path = HTML_DIR) : bool
    {
        $ok    = FALSE;
        $pages = $this->getListOfPages($path);
        $fn    = $pages[$key] ?? FALSE;
        if (file_exists($fn)) {
            $ok = unlink($fn);
        }
        return $ok;
    }
}
