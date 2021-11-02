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
    public static function load_transforms(string $path) : array
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
        return count(self::$container[$class]);
    }
}
