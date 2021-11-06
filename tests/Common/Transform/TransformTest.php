<?php
namespace FileCMSTest\Common\Transform;

use FileCMS\Common\Transform\Transform;
use PHPUnit\Framework\TestCase;
class TransformTest extends TestCase
{
    public $testFileDir = '';
    public function setUp() : void
    {
        $this->testFileDir = realpath(__DIR__ . '/../../test_files');
        Transform::$container = [];
    }
    public function testGetInstanceReturnsNullIfClassEmpty()
    {
        $expected = NULL;
        $actual   = Transform::get_instance('');
        $this->assertEquals($expected, $actual);
    }
    public function testGetInstanceReturnsExpectedInstance()
    {
        $expected = 'ArrayObject';
        $obj      = Transform::get_instance('ArrayObject');
        $actual   = get_class($obj);
        $this->assertEquals($expected, $actual);
    }
    public function testGetInstancePopulatesContainer()
    {
        $expected = 1;
        $obj = Transform::get_instance('ArrayObject');
        $actual = count(Transform::$container);
        $this->assertEquals($expected, $actual);
    }
    public function testLoadTransformsPopulatesContainerWithExpectedNumber()
    {
        $path = __DIR__ . '/../../../src/Transform';
        $expected = count(glob($path . '/*.php'));
        $actual = Transform::load_transforms($path);
        $this->assertEquals($expected, $actual);
    }
    public function testTransformSendsParamsToCallback()
    {
        $params = ['a' => 'AAA'];
        Transform::$container['one'] = function ($text, $params) { return $params['a']; };
        $expected = 'AAA';
        $actual   = Transform::transform('BBB', ['one' => ['callback' => 'one', 'params' => $params]]);
        $this->assertEquals($expected, $actual);
    }
    public function testTransformWorksOkIfNoParams()
    {
        Transform::$container['one'] = function ($text, $params) { return 'AAA'; };
        $expected = 'AAA';
        $actual   = Transform::transform('BBB', ['one' => ['callback' => 'one']]);
        $this->assertEquals($expected, $actual);
    }
    public function testTransformRunsMultipleCallbacks()
    {
        $text = 'THIS IS A TEST';
        $callbacks = [
            'one' => ['callback' => 'one', 'params' => []],
            'two' => ['callback' => 'two', 'params' => []],
        ];
        Transform::$container['one'] = function ($text, $params) { return strtolower($text); };
        Transform::$container['two'] = function ($text, $params) { return ucwords($text); };
        $expected = 'This Is A Test';
        $actual   = Transform::transform($text, $callbacks);
        $this->assertEquals($expected, $actual);
    }
    public function testTransformGetCallbackListAsHtmlReturnsExpectedCount()
    {
        $path = __DIR__ . '/../../../src/Transform';
        Transform::load_transforms($path);
        $html     = Transform::get_callback_list_as_html('h1');
        $expected = count(glob($path . '/*.php'));
        $actual   = substr_count($html, '<h1>');
        $this->assertEquals($expected, $actual);
    }
    public function testExtractCallbacksFromPostReturnsExpectedArray()
    {
        $trans_keys = [
            0 => '7701e7a8b3435ab59ce810156fb9b790',
            1 => 'fbcdd5b6030474a6f732950770e32e9f',
            2 => '2ba450065f34be0db8f7de60d2d1d4e8',
        ];
        $post = [
            '7701e7a8b3435ab59ce810156fb9b790_class' => 'FileCMS%5CTransform%5CTableToDiv',
            '7701e7a8b3435ab59ce810156fb9b790_tr' => 'row',
            '7701e7a8b3435ab59ce810156fb9b790_td' => 'col',
            '7701e7a8b3435ab59ce810156fb9b790_th' => '',
            'ab0ed6542bacdc134a87a83e69f151d0_class' => 'FileCMS%5CTransform%5CRemoveBlock',
            'ab0ed6542bacdc134a87a83e69f151d0_start' => '',
            'ab0ed6542bacdc134a87a83e69f151d0_stop' => '',
            'ab0ed6542bacdc134a87a83e69f151d0_items' => '',
            'fbcdd5b6030474a6f732950770e32e9f_class' => 'FileCMS%5CTransform%5CRemoveAttributes',
            'fbcdd5b6030474a6f732950770e32e9f_attributes' => 'width,height',
            '2ba450065f34be0db8f7de60d2d1d4e8_class' => 'FileCMS%5CTransform%5CReplace',
            '2ba450065f34be0db8f7de60d2d1d4e8_case' => '1',
            '2ba450065f34be0db8f7de60d2d1d4e8_search' => 'TEST',
            '2ba450065f34be0db8f7de60d2d1d4e8_replace' => 'test',
            'd4e03ef06c249dfd3bd5088fba42efec_class' => 'FileCMS%5CTransform%5CClean',
            '3558c470b1f925f143ecb08af79e9400_class' => 'FileCMS%5CTransform%5CPrepend',
            '3558c470b1f925f143ecb08af79e9400_text' => '',
            'c2fd5cc4c9dfde7a2eae9731dcef474f_class' => 'FileCMS%5CTransform%5CAppend',
            'c2fd5cc4c9dfde7a2eae9731dcef474f_text' => '',
            '7b8c2654ec73b6e2ace2fd8f55105d12_class' => 'FileCMS%5CTransform%5CReplaceCallbackArray',
            '7b8c2654ec73b6e2ace2fd8f55105d12_callback_array_file' => '',
            'a69e68d8cc4825659287ae571ed2922c_class' => 'FileCMS%5CTransform%5CReplaceRepeat',
            'a69e68d8cc4825659287ae571ed2922c_search' => '',
            'a69e68d8cc4825659287ae571ed2922c_replace' => '',
            '47768717097dee0e0657379eb7d5c395_class' => 'FileCMS%5CTransform%5CReplaceRegex',
            '47768717097dee0e0657379eb7d5c395_regex' => '',
            '47768717097dee0e0657379eb7d5c395_replace' => '',
            'choose_transform' => 'Transform'
        ];
        $expected = ['FileCMS\Transform\TableToDiv','FileCMS\Transform\RemoveAttributes','FileCMS\Transform\Replace'];
        $extract  = Transform::extract_callbacks_from_post($trans_keys, $post);
        $actual   = array_keys($extract);
        $this->assertEquals($expected, $actual);
    }
    public function testLoadTransformUsingTableToDivCallbacksProducesExpectedResults()
    {
        $contents = file_get_contents($this->testFileDir . '/test6.html');
        $callbacks = [
            'TableToDiv' => [
                'callback' => 'FileCMS\Transform\TableToDiv',
                'params'   => ['tr' => 'row', 'td' => 'col', 'th' => 'col bold']
            ],
        ];
        $expected = '<h1>Table Test</h1><div class="row"><div class="col">Item</div><div class="col">Status</div><div class="col">Notes</div></div><div class="row"><div class="col">one</div><div class="col">1</div><div class="col">blah blah blah</div></div><div class="row"><div class="col">two</div><div class="col">0</div><div class="col">blah blah blah</div></div><div class="row"><div class="col">three</div><div class="col">1</div><div class="col">blah blah blah</div></div>';
        $actual   = Transform::transform($contents, $callbacks);
        $this->assertEquals($expected, $actual);
    }
    public function testLoadTransformUsingTwoCallbacksProducesExpectedResults()
    {
        $contents = file_get_contents($this->testFileDir . '/test7.html');
        $callbacks = [
            'Replace' => [
                'callback' => 'FileCMS\Transform\Replace',
                'params'   => ['search' => 'ul>', 'replace' => 'ol>']
            ],
            'RemoveBlock' => [
                'callback' => 'FileCMS\Transform\RemoveBlock',
                'params'   => ['start' => '<i>', 'stop' => '</i>', 'items' => ['WILL BE','FOR SURE']]
            ],
        ];
        $expected = '<h1>Testing Multiple Callbacks</h1><ol>    <li>One</li>    <li>Two</li>    <li>Three</li></ol><p></p><p><i>This WILL NOT be removed</i></p>';
        $actual   = Transform::transform($contents, $callbacks);
        $this->assertEquals($expected, $actual);
    }
}
