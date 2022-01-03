<?php
namespace FileCMS\Common\View;

use ArrayIterator;
use LimitIterator;
use RecursiveDirectoryIterator;
class Html
{
    const DEFAULT_CARD_DIR = 'cards';
    const DEFAULT_LAYOUT   = BASE_DIR . '/layouts/layout.html';
    const DEFAULT_HOME     = HTML_DIR . '/home.phtml';
    const DEFAULT_DELIM    = '%%';
    const DEFAULT_EXT      = ['html', 'htm'];
    const FALLBACK_HOME    = 'fallback.html';
    public $uri     = '';
    public $htmDir  = '';
    public $cardDir = '';
    public $delim   = '';
    public $config  = [];
    public $allowed = [];   // allowed extensions
    public function __construct(array $config, string $uri, string $htmlDir)
    {
        $this->config  = $config;
        $this->uri     = $uri;
        $this->htmlDir = $htmlDir;
        $this->cardDir = $config['CARDS'] ?? static::DEFAULT_CARD_DIR;
        $this->delim   = $config['DELIM'] ?? static::DEFAULT_DELIM;
        $this->allowed = $config['SUPER']['allowed_ext'] ?? self::DEFAULT_EXT;
    }
    /**
     * Produces HTML snippet injected into layout
     *
     * @param string $body : existing body if any
     * @return string $html : full HTML page
     */
    public function render(string $body = '')
    {
        // pull in layout and body
        $msg    = '';
        $output = '';
        $card   = '';
        $layout = $this->config['LAYOUT'] ?? static::DEFAULT_LAYOUT;
        $fn     = str_replace('//', '/', $layout);
        $layout = file_get_contents($fn);
        // inject meta + title tags
        $meta = $this->config['META'][$this->uri] ?? $this->config['META']['default'] ?? [];
        foreach ($meta as $tag => $val) {
            $this->injectMeta($layout, $tag, $val);
        }
        // work with body: if html, just read contents
        $body = $this->partial($body);
        // render and deliver final output
        $search   = $this->delim . 'CONTENTS' . $this->delim;
        $output   = str_replace($search, $body, $layout);
        // replace message if present
        if ($msg && strpos($output, $this->config['MSG_MARKER'])) {
            $msg = '<div class="row justify-content-between">' . $msg . '</div>' . PHP_EOL;
            $output = str_replace($this->config['MSG_MARKER'], $msg, $output);
        }
        return $output;
    }
    /**
     * Produces partial HTML snippet to be injected into layout
     *
     * @param string $body : existing body if any
     * @return string $html : full HTML page
     */
    public function partial(string $body = '')
    {
        // pull in layout and body
        $msg    = '';
        $output = '';
        // work with body: if html, just read contents
        if (empty($body)) {
            // try HTML and HTM extensions
            foreach ($this->allowed as $ext) {
                $bodyFn = $this->htmlDir . $this->uri . '.' . $ext;
                if (file_exists($bodyFn)) {
                    $body = file_get_contents($bodyFn);
                    break;
                }
            }
            // if $body still not populated, try PHTML
            if (!$body) {
                $bodyFn = $this->htmlDir . $this->uri . '.phtml';
                // if phtml, use "include" + output buffering to grab contents
                if (file_exists($bodyFn)) {
                    $body = $this->runPhpFile($bodyFn);
                // fallback: just go home
                } else {
                    $this->uri = '/home';
                    $home   = $this->config['HOME'] ?? self::DEFAULT_HOME;
                    $bodyFn = $this->htmlDir . '/' . $home;
                    if (substr($bodyFn, -5) === 'phtml') {
                        $body = $this->runPhpFile($bodyFn);
                    } else {
                        $body = file_get_contents($bodyFn);
                    }
                }
            }
        }
        // inject cards into body
        if (stripos($body, $this->delim) !== FALSE) {
            $search = '!' . $this->delim . '(.+?)' . $this->delim . '!i';
            $body = preg_replace_callback($search, [$this, 'injectCards'], $body);
        }
        return $body;
    }
    /**
     * Populates <HEAD> section with  meta tags and title
     *
     * @param string $body : final HTML to be produced (by ref)
     * @param string $tag  : meta tag we're working on
     * @param string $val  : value to be replaced
     * @return int   $repl : number of replacements made
     */
    public function injectMeta(string &$body, string $tag, string $val)
    {
        $repl = 0;
        $search = $this->delim . strtoupper($tag) . $this->delim;
        if (strpos($body, $search)) {
            $body = str_replace($search, $val, $body);
            $repl++;
        }
        return $repl;
    }
    /**
     * Populates body with cards
     * Called from preg_replace_callback()
     *
     * @param array $match : what got matched during this pass
     * @return string $body : HTML w/ cards injected
     */
    public function injectCards(array $match)
    {
        $card = '';
        $item = $match[1] ?? '';
        if ($item === FALSE) return '';
        // figure out if matched item is stand-alone, or has args
        if (strpos($item, '=') !== FALSE) {
            [$dir, $qualifier] = explode('=', $item);
            $qualifier = trim($qualifier);
        } else {
            $dir = $item;
            $qualifier = '';
        }
        $dir = $this->getDir($dir);
        // randomize linked list of cards
        if (empty($qualifier)) {
            $iter = $this->getCardIterator($dir);
        } else {
            // otherwise look for ","
            if (strpos($qualifier, ',') !== FALSE) {
                $iter = $this->getOrderedCardIterator($dir, $qualifier);
            // is a number?
            } elseif (ctype_digit((string) $qualifier)) {
                $iter = $this->getCardIterator($dir, $this->cardDir);
                if (empty($iter)) {
                    $iter = FALSE;
                } else {
                    $temp = clone $iter;
                    $iter = new LimitIterator($temp, 0, (int) $qualifier);
                    $iter->rewind();
                }
            // is a single card?
            } elseif (strlen($qualifier) > 0) {
                $iter = $this->getOrderedCardIterator($dir, $qualifier);
            } else {
                $iter = FALSE;
            }
        }
        // loop through iteration
        if ($iter !== FALSE) {
            while ($iter->valid()) {
                $fn = $iter->current();
                $card .= (file_exists($fn)) ? file_get_contents($fn) : '';
                $iter->next();
            }
        }
        return $card;
    }
    /**
     * Produces confirmed directory path
     *
     * @param string $dir   : relative directory
     * @return string $path : absolute directory path or '' if not found
     */
    public function getDir(string $dir)
    {
        $path = str_replace('//', '/', $this->htmlDir . '/' . $dir);
        if (!file_exists($path)) {
            $path = str_replace('//', '/', $this->htmlDir . '/' . strtolower($dir));
        }
        return (file_exists($path)) ? $path : '';
    }
    /**
     * Produces randomized iteration of cards
     *
     * @param string $dir  : current card dir we're working on
     * @return ArrayIterator $list | bool FALSE
     */
    public function getCardIterator(string $dir)
    {
        $iter  = FALSE;
        $cards = [];
        $temp  = [];
        $dir   = str_replace('//', '/', $dir . '/' . $this->cardDir);
        $list  = glob($dir . '/*');
        if ($list) {
            foreach ($list as $fn)
                $cards[substr(basename($fn), 0, -5)] = $fn;
            $linked = array_keys($cards);
            shuffle($linked);
            foreach ($linked as $key)
                $temp[$key] = $cards[$key];
            $iter = new ArrayIterator($temp);
        }
        return $iter;
    }
    /**
     * Produces ordered iteration of cards
     *
     * @param string $dir       : current card dir we're working on
     * @param string $qualifier : something like "adding_intelligence,talk_to_users,etc."
     * @return ArrayIterator $iter | bool FALSE
     */
    public function getOrderedCardIterator(string $dir, string $qualifier)
    {
        // build out directory path
        $list = explode(',', $qualifier);
        $path = $dir . '/' . $this->cardDir;
        foreach ($list as $key => $value) {
            $value = trim($value);
            $value .= (substr($value, -4) !== 'html') ? '.html' : '';
            $list[$key] = str_replace(['//','..'], ['/','.'], $path . '/' . $value);
        }
        // return new iterator
        $iter = new ArrayIterator(array_values($list));
        return $iter;
    }
    /**
     * Produces output from "phtml" or "php" files
     *
     * @param string $fn
     * @return string HTML
     */
    protected function runPhpFile(string $fn)
    {
        $OBJ = $this;
        ob_start();
        include $fn;
        $body = ob_get_contents();
        ob_end_clean();
        return $body;
    }
}
