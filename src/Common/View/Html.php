<?php
namespace SimpleHtml\Common\View;

use ArrayIterator;
use LimitIterator;
use RecursiveDirectoryIterator;
class Html
{
    const DEFAULT_CARD_DIR = 'cards';
    const DEFAULT_LAYOUT   = BASE_DIR . '/layout.html';
    const DEFAULT_HOME     = 'index.html';
    const DEFAULT_DELIM    = '%%';
    const DEFAULT_EXT      = ['html', 'htm'];
    public $uri    = '';
    public $htmDir = '';
    public $delim  = '';
    public $config = [];
    public $allowed = [];   // allowed extensions
    public function __construct(array $config, string $uri, string $htmlDir)
    {
        $this->config  = $config;
        $this->uri     = $uri;
        $this->htmlDir = $htmlDir;
        $this->delim   = $config['DELIM'] ?? static::DEFAULT_DELIM;
        $this->allowed = $config['SUPER']['allowed_ext'] ?? self::DEFAULT_EXT;
    }
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
        if (!$body) {
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
        try {
            // inject cards into body
            $card_dir = $this->config['CARDS'] ?? self::DEFAULT_CARD_DIR;
            $iter = new RecursiveDirectoryIterator($this->htmlDir);
            foreach ($iter as $dir => $obj) {
                $name = $obj->getBasename();
                if ($obj->isDir() && $name !== '.' && $name !== '..')
                    $body = $this->injectCards($body, $dir, $card_dir);
            }
        } catch (Throwable $t) {
            error_log($t);
        }

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
     *
     * @param string $body : final HTML to be produced (by ref)
     * @param string $dir  : name of current card dir we're working on
     * @param string $card_dir : directory name for cards (default "cards")
     * @return string $body : HTML w/ cards injected
     */
    public function injectCards(string $body, string $dir, string $card_dir)
    {
        // randomize linked list of cards
        $name = basename($dir);
        $search = $this->delim . strtoupper($name);
        if (stripos($body, $search) !== FALSE) {
            $search = '!' . $this->delim . strtoupper($name) . '(.*?)?' . $this->delim . '!i';
            preg_match($search, $body, $matches);
            $qualifier = $matches[1] ?? '';
            $qualifier = trim($qualifier);
            // randomize linked list of cards
            if (empty($qualifier)) {
                $iter = $this->getCardIterator($dir, $card_dir);
            } else {
                // get rid of "="
                $qualifier = trim(str_replace('=', '', $qualifier));
                // is it just a number?
                if (((int) $qualifier) > 0) {
                    $iter = $this->getCardIterator($dir, $card_dir);
                    $temp = clone $iter;
                    $iter = new LimitIterator($temp, 0, (int) $qualifier);
                    $iter->rewind();
                // otherwise look for ","
                } elseif (strpos($qualifier, ',') !== FALSE) {
                    $iter = $this->getOrderedCardIterator($dir, $card_dir, $qualifier);
                } else {
                    $iter = FALSE;
                }
            }
            // loop through iteration
            if ($iter !== FALSE) {
                $card = '';
                while ($iter->valid()) {
                    $fn = $iter->current();
                    $card .= (file_exists($fn)) ? file_get_contents($fn) : '';
                    $iter->next();
                }
                $body = str_replace($matches[0], $card, $body);
            }
        }
        return $body;
    }
    /**
     * Produces randomized iteration of cards
     *
     * @param string $dir  : current card dir we're working on
     * @param string $card_dir : directory name for cards
     * @return ArrayIterator $list | bool FALSE
     */
    public function getCardIterator(string $dir, string $card_dir)
    {
        $iter  = FALSE;
        $cards = [];
        $temp  = [];
        $dir   = str_replace('//', '/', $dir . '/' . $card_dir);
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
     * @param string $card_dir  : directory name for cards
     * @param string $qualifier : something like "adding_intelligence,talk_to_users,etc."
     * @return ArrayIterator $iter | bool FALSE
     */
    public function getOrderedCardIterator(string $dir, string $card_dir, string $qualifier)
    {
        // build out directory path
        $list = explode(',', $qualifier);
        $path = $dir . '/' . $card_dir;
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
