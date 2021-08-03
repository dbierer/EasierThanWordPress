<?php
namespace SimpleHtml\Common\Generic;

use ArrayObject;
class Registry
{
    public static $storage = NULL;
    public static function getStorage()
    {
        if (empty(self::$storage)) {
            self::$storage = new ArrayObject();
        }
        return self::$storage;
    }
    public static function setItem(string $key, $value) : void
    {
        $storage = self::getStorage();
        $storage->offsetSet($key, $value);
    }
    public static function getItem(string $key)
    {
        $storage = self::getStorage();
        return $storage->offsetGet($key) ?? NULL;
    }
}
