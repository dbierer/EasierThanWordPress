<?php
namespace FileCMSTest\Common\View;

use FileCMS\Common\Transform\TransformInterface;
use FileCMS\Common\View\Html;
use FileCMS\Common\Generic\Messages;
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
        $this->config = include BASE_DIR . '/tests/config/test.config.php';
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
  <title>FileCMS</title>
</head>
EOT;
        $this->html->injectMeta($body, 'title', 'FileCMS');
        $actual = $body;;
        $this->assertEquals($expected, $actual, 'Meta tag not injected');
    }
    public function testGetDirReturnsEmptyIfPathNotFound()
    {
        $dir = 'xyz';
        $expected = '';
        $actual = $this->html->getDir($dir);
        $this->assertEquals($expected, $actual);
    }
    public function testGetDirReturnsCorrectPath()
    {
        $dir = 'blog';
        $expected = BASE_DIR . '/templates/site/blog';
        $actual = $this->html->getDir($dir);
        $this->assertEquals($expected, $actual);
    }
    public function testGetDirReturnsCorrectPathIfDirUppercase()
    {
        $dir = 'BLOG';
        $expected = BASE_DIR . '/templates/site/blog';
        $actual = $this->html->getDir($dir);
        $this->assertEquals($expected, $actual);
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
        $dir     = $this->html->getDir('blog');
        $iter    = $this->html->getOrderedCardIterator($dir, 'one,two,three');
        $copy    = $iter->getArrayCopy();
        $real    = glob(BASE_DIR . '/templates/site/blog/cards/*.html');
        $actual   = array_diff($real, $copy);
        $expected = [BASE_DIR . '/templates/site/blog/cards/four.html'];
        $this->assertEquals($expected, $actual, 'Ordered results not produced');
    }
    public function testPartialSingle()
    {
        $body     = '<html><body>%%BLOG=1%%</body></html>';
        $body     = $this->html->partial($body);
        $expected = TRUE;
        $actual   = (bool) strpos($body, '<h3 class="card-title">');
        $this->assertEquals($expected, $actual, 'Card not injected');
    }
    public function testPartialTriple()
    {
        $body     = '<html><body>%%BLOG=3%%</body></html>';
        $dir      = BASE_DIR . '/templates/site/blog';
        $body     = $this->html->partial($body);
        $expected = 3;
        $actual   = substr_count($body, '<h3 class="card-title">');
        $this->assertEquals($expected, $actual, 'Multiple cards not injected properly');
    }
    public function testPartialOrdered()
    {
        $body     = '<html><body>%%BLOG=one,two,three%%</body></html>';
        $dir      = BASE_DIR . '/templates/site/blog';
        $body     = $this->html->partial($body);
        $expected = ['<a href="/blog/one">Card One</a>','<a href="/blog/two">Card Two</a>','<a href="/blog/three">Card Three</a>'];
        $pattern  = '!>(.*?)</h3>!';
        $matches  = [];
        preg_match_all($pattern, $body, $matches);
        $actual   = $matches[1] ?? 'Fail';
        $this->assertEquals($expected, $actual, 'Multiple cards not injected in order');
    }
    public function testPartialSingleByName()
    {
        $body     = '<html><body>%%BLOG=one%%</body></html>';
        $dir      = BASE_DIR . '/templates/site/blog';
        $body     = $this->html->partial($body);
        $expected = '<a href="/blog/one">Card One</a>';
        $pattern  = '!>(.*?)</h3>!';
        $matches  = [];
        preg_match($pattern, $body, $matches);
        $actual   = $matches[1] ?? 'Fail';
        $this->assertEquals($expected, $actual, 'Single card by name not injected');
    }
    public function testPartialIgnoresCardsIfCardsFlagSet()
    {
        $body     = '<html><body>%%BLOG%%</body></html>';
        $actual   = $this->html->partial($body, FALSE);
        $expected = $body;
        $this->assertEquals($expected, $actual, 'Card injected despite flag being set FALSE');
        $expected = TRUE;
        $actual   = (bool) strpos($actual, '%%BLOG%%');
        $this->assertEquals($expected, $actual, 'Cards marker is missing');
    }
    public function testRender()
    {
        $expected = TRUE;
        $html     = $this->html->render();
        $actual   = (bool) strpos($html, 'Business Name or Tagline');
        $this->assertEquals($expected, $actual);
    }
    public function testRenderDoesNotInjectCardsIfFlagSet()
    {
        $expected = TRUE;
        $html     = $this->html->render('', FALSE);
        $actual   = (bool) strpos($html, '%%BLOG=3%%');
        $this->assertEquals($expected, $actual);
    }
    public function testRenderReplacesMessageMarker()
    {
        $layout = HTML_DIR . '/testM.html';
        $this->config['LAYOUT'] = $layout;
        $this->html   = new Html($this->config, 'testM', HTML_DIR);
        $marker = $this->config['MSG_MARKER'];
        $body = file_get_contents($layout);
        $expected = <<<EOT
<h1>Test M</h1>
<hr />
<p>Messages</p>
TEST
EOT;
        $this->html->msg = 'TEST';
        $actual = $this->html->render($body);
        $this->assertEquals($expected, trim($actual));
    }
}

