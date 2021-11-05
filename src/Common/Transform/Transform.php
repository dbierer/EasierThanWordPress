<?php
namespace FileCMS\Common\Transform;

/*
 * FileCMS\Common\Transform\Transform
 *
 * @description performs search and replace using str_replace() or str_ireplace()
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
use RecursiveDirectoryIterator;
use FileCMS\Common\Page\Edit;
use FileCMS\Common\Generic\Messages;
class Transform
{
    const CONFIG_KEY = 'TRANSFORM';
    const NOT_AVAILABLE = 'Not Available';
    const ERR_NO_PAGES = 'ERROR: no pages chosen';
    const ERR_NO_TRANS = 'ERROR: no transforms chosen';
    const ERR_TRANS_UNABLE = 'ERROR: unable to extract transforms from post';
    public static $container = [];
    /**
     * Grabs contents, applies transforms
     * NOTE: removes any CR/LF
     *
     * @param string $contents    : contents to be transformed
     * @param array  $callbacks   : array of transform callbacks; expects key "callback"
     * @return string $finished   : transformed text
     */
    public static function transform(string $contents, array $callbacks = [])
    {
        $text = str_replace(PHP_EOL, '', trim($contents));
        if (!empty($text) && !empty($callbacks)) {
            foreach ($callbacks as $key => $item) {
                $class  = $item['callback'] ?? '';
                $params = $item['params'] ?? [];
                $obj    = self::get_instance($class, $params);
                $text   = (!empty($obj)) ? $obj($text, $params) : $text;
            }
        }
        return $text;
    }
    /**
     * Gets instance of callback from self::$container
     *
     * @param string $class : class to be instantiated
     * @return TransformInterface $obj | NULL
     */
    public static function get_instance(string $class)
    {
        if ($class === '') return NULL;
        if (empty(self::$container[$class]))
            self::$container[$class] = new $class();
        return self::$container[$class] ?? NULL;
    }
    /**
     * Loads transforms from classes in $path
     *
     * @param string $path : directory to find transforms
     * @return int $num    : count of self::$container
     */
    public static function load_transforms(string $path) : int
    {
        if (file_exists($path)) {
            $iter = new RecursiveDirectoryIterator($path);
            foreach ($iter as $name => $obj) {
                if ($obj->getExtension() === 'php') {
                    $contents = file_get_contents($name);
                    preg_match('!namespace (.+?);!', $contents, $match);
                    if (!empty($match[1])) {
                        $namespace = trim($match[1]);
                        preg_match('!class (.+?)\b!', $contents, $match);
                        if (!empty($match[1])) {
                            $class = $namespace . '\\' . $match[1];
                            self::get_instance($class);
                        }
                    }
                }
            }
        }
        return count(self::$container);
    }
    /**
     * Returns callback list formatted as HTML
     *
     * @param string $tag : HTML header tag
     * @return string $html
     */
    public static function get_callback_list_as_html(string $tag = 'h4')
    {
        $html = '';
        foreach (self::$container as $class => $obj) {
            $transform_key = md5($class);
            $temp = explode('\\', $class);
            $show = array_pop($temp);
            $html .= '<br />'
                    . '<' . $tag . '>'
                    . '<input type="checkbox" name="choose[]" value="' . $transform_key . '" title="Check this box to use this transform" />'
                    . '<input type="hidden" name="' . $transform_key . '_class" value="' . urlencode($class) . '" />'
                    . '&nbsp;'
                    . $show
                    . '</' . $tag . '>';
            $html .= '<table>';
            if (is_object($obj) && $obj instanceof TransformInterface) {
                $params = get_object_vars($obj);
                foreach ($params as $key => $value) {
                    $sub_key = $transform_key . '_' . $key;
                    $html .= '<tr>';
                    $html .= '<th>' . $key . '</th>';
                    $html .= '<td><input name="' . $sub_key . '" placeholder="' . gettype($value) . '"></td>';
                    $html .= '</tr>';
                }
            } else {
                $html .= '<tr><th>' . $key . '</th><td>' . self::NOT_AVAILABLE . '</td></tr>';
            }
            $html .= '</table>';
            $html .= $obj::DESCRIPTION;
        }
        return $html;
    }
    /**
     * Returns array of callbacks ready for processing from HTML
     *
     * @param array $trans_keys : array of transform keys
     * @param array $post       : $_POST
     * @return array $transform : [class => ['callback' => string, 'params' => array]]
     */
     public static function extract_callbacks_from_post(array $trans_keys, array $post) : array
     {
        $transform = [];
        foreach ($trans_keys as $hash) {
            $class = $post[$hash . '_class'] ?? '';
            $class = urldecode($class);
            if (!empty($class)) {
                $transform[$class] = ['callback' => $class, 'params' => []];
                foreach ($post as $key => $value) {
                    if (strpos($key, $hash . '_') === 0) {
                        $item = trim(str_replace($hash . '_', '', $key));
                        if (!empty($item) && $item !== 'class') {
                            $transform[$class]['params'][$item] = urldecode($value);
                        }
                    }
                }
            }
        }
        return $transform;
     }
}
