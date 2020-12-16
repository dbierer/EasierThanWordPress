<?php
namespace Common\View;

use LimitIterator;
use RecursiveDirectoryIterator;
class Html
{
    const DEFAULT_CARD_DIR = 'cards';
    public $uri    = '';
    public $htmDir = '';
    public $config = [];
    public function __construct(array $config, string $uri, string $htmlDir)
    {
        $this->config  = $config;
        $this->uri     = $uri;
        $this->htmlDir = $htmlDir;
    }
    public function render(string $body = '')
    {
        // pull in layout and body
        $OBJ    = $this;
        $msg    = '';
        $output = '';
        $card   = '';
        $fn     = str_replace('//', '/', $this->htmlDir . '/' . $this->config['LAYOUT']);
        $layout = file_get_contents($fn);
        // inject meta + title tags
        $meta = $this->config['META'][$this->uri] ?? $this->config['META']['default'];
        foreach ($meta as $tag => $val) {
            $this->injectMeta($layout, $tag, $val);
        }
        // work with body: if html, just read contents
        if (!$body) {
            $bodyFn = $this->htmlDir . $this->uri . '.html';
            if (file_exists($bodyFn)) {
                $body = file_get_contents($bodyFn);
            } else {
                $bodyFn = $this->htmlDir . $this->uri . '.phtml';
                // if phtml, use "include" + output buffering to grab contents
                if (file_exists($bodyFn)) {
                    ob_start();
                    include $bodyFn;
                    $body = ob_get_contents();
                    ob_end_clean();
                // fallback: just go home
                } else {
                    $this->uri = '/home';
                    $bodyFn = $this->htmlDir . '/home.html';
                    $body = file_get_contents($bodyFn);
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
        $search   = $this->config['DELIM'] . 'CONTENTS' . $this->config['DELIM'];
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
        $search = $this->config['DELIM'] . strtoupper($tag) . $this->config['DELIM'];
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
        $search = $this->config['DELIM'] . strtoupper($name);
        if (stripos($body, $search) !== FALSE) {
            $search = '!' . $this->config['DELIM'] . strtoupper($name) . '(=\d+?)?' . $this->config['DELIM'] . '!i';
            preg_match($search, $body, $matches);
            // randomize linked list of cards
            $iter = $this->getCardIterator($dir, $card_dir);
            if ($iter !== FALSE) {
                if (!empty($matches[1])) {
                    $max = (int) str_replace('=', '', $matches[1]);
                    $temp = clone $iter;
                    $iter = new LimitIterator($temp, 0, $max);
                    $iter->rewind();
                }
                $card = '';
                while ($iter->valid()) {
                    $card .= file_get_contents($iter->current());
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
            $iter = new \ArrayIterator($temp);
        }
        return $iter;
    }
}
