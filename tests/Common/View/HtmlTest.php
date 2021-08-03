<?php
namespace SimpleHtmlTest\Common\View;

use SimpleHtml\Common\View\Html;
use PHPUnit\Framework\TestCase;
class HtmlTest extends TestCase
{
    const DEFAULT_CARD_DIR = 'cards';
    public $html   = NULL;
    public $uri    = '';
    public $htmDir = '';
    public $config = [];
    public function setup() : void
    {
        $this->config  = include BASE_DIR . '/src/config/config.php';
    }
    public function testConfigHasDelimCardsAndLayoutKeys()
    {
        $expected = TRUE;
        $actual   = isset($this->config['DELIM']);
        $this->assertEquals($expected, $actual, 'DELIM config key missing');
        $actual   = isset($this->config['CARDS']);
        $this->assertEquals($expected, $actual, 'CARDS config key missing');
        $actual   = isset($this->config['LAYOUT']);
        $this->assertEquals($expected, $actual, 'LAYOUT config key missing');
    }
    /*
    public function testRender()
    {
        $this->html = new Html($this->config, '/home', HTML_DIR);
        // string $body = ''
    }
    public function testInjectMeta()
    {
        // string &$body, string $tag, string $val
    }
    public function testInjectCards()
    {
        //string $body, string $dir, string $card_dir
    }
    public function testGetCardIterator()
    {
        // string $dir, string $card_dir
    }
    public function testGetOrderedCardIterator()
    {
        // string $dir, string $card_dir, string $qualifier
    }
    */
}

