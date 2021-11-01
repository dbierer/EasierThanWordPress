<?php
namespace FileCMS\Common\Contact;

class Base
{
    /**
     * Filters data for $table
     *
     * @param string $table : table name
     * @param array $post   : usually from $_POST
     * @param array $config
     * @return array $inputs : filtered
     */
    public static function filter(string $table, array $post, array $config)
    {
        $callbacks = $config['FILTERS'][$table] ?? [];
        $temp = [];
        foreach ($callbacks as $key => $func) {
            if (empty($post[$key])) {
                $post[$key] = '';
            } elseif (is_array($post[$key])) {
                $post[$key] = json_encode($post[$key]);
            }
            $temp[$key] = $func($post[$key]);
        }
        return $temp;
    }
}
