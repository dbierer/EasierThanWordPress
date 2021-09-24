<?php
namespace SimpleHtml\Common\Generic;

class Messages
{
    const MSG_KEY = 'messages';
    public $messages = NULL;
    protected static $_instance = NULL;
    private function __construct()
    {
        if (!empty($_SESSION[self::MSG_KEY])) {
            $this->messages = unserialize($_SESSION[self::MSG_KEY]);
        }
    }
    public static function getInstance()
    {
        if (self::$_instance === NULL) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function addMessage($value)
    {
        $this->messages[] = $value;
    }
    public function getMessages()
    {
        if (empty($this->messages)) {
            $msg = NULL;
        } else {
            $msg = implode("<br />\n", $this->messages);
            $this->messages = [];
        }
        return $msg;
    }
    public function __destruct()
    {
        $_SESSION[self::MSG_KEY] = serialize($this->messages);
    }
}
