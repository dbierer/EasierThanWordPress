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
        $this->config = include BASE_DIR . '/src/config/config.php';
        $this->html   = new Html($this->config, '/home', BASE_DIR . '/templates/site');
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
    public function testInjectMetaTag()
    {
        $body = <<<EOT
<head>
  <title>%%TITLE%%</title>
</head>
EOT;
        $expected = <<<EOT
<head>
  <title>SimpleHtml</title>
</head>
EOT;
        $this->html->injectMeta($body, 'title', 'SimpleHtml');
        $actual = $body;;
        $this->assertEquals($expected, $actual, 'Meta tag not injected');
    }
    public function testGetCardIteratorReturnsIterator()
    {
        $expected = 'ArrayIterator';
        $dir      = BASE_DIR . '/templates/site/blog';
        $iter     = $this->html->getCardIterator($dir, 'cards');
        $actual   = get_class($iter);
        $this->assertEquals($expected, $actual, 'ArrayIterator not produced');
    }
    public function testGetOrderedCardIterator()
    {
        $expected = 'one.html two.html three.html';
        $dir      = BASE_DIR . '/templates/site/blog';
        $iter     = $this->html->getOrderedCardIterator($dir, 'cards', 'one,two,three');
        $actual   = '';
        foreach ($iter as $item)
            $actual .= basename($item) . ' ';
        $actual   = trim($actual);
        $this->assertEquals($expected, $actual, 'Ordered results not produced');
    }
    public function testInjectCardsSingle()
    {
        $body     = '<html><body>%%BLOG=1%%</body></html>';
        $dir      = BASE_DIR . '/templates/site/blog';
        $body     = $this->html->injectCards($body, $dir, 'cards');
        $expected = TRUE;
        $actual   = (bool) strpos($body, '<h3 class="card-title">');
        $this->assertEquals($expected, $actual, 'Card not injected');
    }
    public function testInjectCardsTriple()
    {
        $body     = '<html><body>%%BLOG=3%%</body></html>';
        $dir      = BASE_DIR . '/templates/site/blog';
        $body     = $this->html->injectCards($body, $dir, 'cards');
        $expected = 3;
        $actual   = substr_count($body, '<h3 class="card-title">');
        $this->assertEquals($expected, $actual, 'Multiple cards not injected properly');
    }
    public function testInjectCardsOrdered()
    {
        $body     = '<html><body>%%BLOG=one,two,three%%</body></html>';
        $dir      = BASE_DIR . '/templates/site/blog';
        $body     = $this->html->injectCards($body, $dir, 'cards');
        $expected = ['Card One','Card Two','Card Three'];
        $pattern  = '!>(.*?)</h3>!';
        $matches  = [];
        preg_match_all($pattern, $body, $matches);
        $actual   = $matches[1] ?? 'Fail';
        $this->assertEquals($expected, $actual, 'Multiple cards not injected in order');
    }
    /*
    public function testRender()
    {
        $this->html = new Html($this->config, '/home', HTML_DIR);
        // string $body = ''
    }
    */
}

