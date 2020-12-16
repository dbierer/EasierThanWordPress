<?php
namespace Common\Contact;

class Base
{
    /**
     * Filters data for $table
     *
     * @param string $table : table name
     * @param array $inputs : usually from $_POST
     * @return array $inputs : filtered
     */
    public function filter(string $table, array $inputs)
    {
        $callbacks = $this->config['FILTERS'][$table] ?? [];
        $temp = [];
        foreach ($callbacks as $key => $func) {
            if (!empty($inputs[$key])) {
                if (is_array($inputs[$key])) {
                    $inputs[$key] = json_encode($inputs[$key]);
                }
            }
            $temp[$key] = $func($inputs[$key]);
        }
        return $temp;
    }
}
