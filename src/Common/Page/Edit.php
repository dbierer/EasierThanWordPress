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
    const SUCCESS_SAVE = 'SUCCESS: page saved successfully';
    const ERROR_SAVE   = 'ERROR: unable to save page';
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
        return $key;
    }
    /**
     * Returns filename from key
     *
     * @param $key        : sanitized filename
     * @param string $path : usually HTML_DIR
     * @return string $fn   : filename
     */
    public function getFilenameFromKey(string $key, string $path)
    {
        $key = str_replace($path, '', $fn);
        if ($key[0] !== '/') $key = '/' . $key;
        foreach ($this->allowed as $ext)
            $key = str_ireplace('.' . $ext, '', $key);
        return $key;
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
     * @param string $path      : starting path (if other than HTML_DIR
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
     * @param string $path  : starting path (if other than HTML_DIR
     * @return string $key  : key in $this->pages
     */
    public function getKeyFromURL(string $url, string $path = HTML_DIR) : string
    {
        $key  = parse_url($url, PHP_URL_PATH) ?? ' ';
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
    public function save(string $key, string $contents) : bool
    {
        // use Tidy to sanitize
        $ok = 0;
        if (function_exists('tidy_repair_string')) {
            $fixed = tidy_repair_string($contents);
            // extract content between <body>*</body> tags
            [$first, $last] = explode('<body>', $fixed);
            $pos   = strpos('</body>', $last);
            $contents = substr($last, 0, $pos);
        }
        // check to see if it's an existing file
        $pages = $this->getListOfPages($path);
        $fn    = $pages[$key] ?? '';
        // file already exists, overwrite it
        if (file_exists($fn)) {
            $ok = file_put_contents($fn, $contents);
        } else {
            // if key doesn't exist, it's a new file
            // split key to get directory and filename
            // create directory if needed
            // save to file
        }
        return (bool) $ok;
    }
}
