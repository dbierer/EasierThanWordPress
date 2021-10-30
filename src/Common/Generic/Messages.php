<?php
namespace EasierThanWordPress\Common\Generic;

class Messages
{
    const MSG_KEY = 'messages';
    const EDIT = 'Edit as appropriate and click "Save" when done';
    const LOGIN = 'Enter your login information';
    const CHOOSE = 'Select page to edit or delete.  To add a new page, enter a URL and click "Add New Page" button.';
    const NO_DESCRIPTION = 'Description is not available';
    const ERROR_AUTH = 'ERROR: Unable to authenticate';
    const ERROR_LOGIN = 'ERROR: Unable to login';
    const SUCCESS_LOGOUT = 'SUCCESS: successful logout';
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
            $msg = implode("<br />\n", array_reverse($this->messages));
            $this->messages = [];
        }
        return $msg;
    }
    public function __destruct()
    {
        $_SESSION[self::MSG_KEY] = serialize($this->messages);
    }
}
