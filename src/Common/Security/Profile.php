<?php
namespace SimpleHtml\Common\Security;

class Profile
{
    const PROFILE_KEY = 'profile';
    const PROFILE_DEFAULT = 'default';
    /**
     * Produces an MD5 hash of key $_SERVER info
     * Also stores hash in $_SESSION[PROFILE_KEY]
     *
     * @param array $config
     * @return string $hash : md5() hash
     */
    public static function build(array $config)
    {
        $info = '';
        $keys = $config['super']['profile'] ?? [];
        if (empty($keys)) {
            $info = $_SERVER['HTTP_USER_AGENT'] ?? self::PROFILE_DEFAULT;
        } else {
            foreach ($keys as $idx)
                $info .= $_SERVER[$idx] ?? self::PROFILE_DEFAULT;
        }
        return md5($info);
    }
    /**
     * Sets $_SESSION[PROFILE_KEY] to NULL
     *
     * @return void
     */
    public static function logout()
    {
        $_SESSION[self::PROFILE_KEY] = NULL;
    }
    /**
     * Saves MD5 hash in $_SESSION[PROFILE_KEY]
     *
     * @param string $hash : generated hash
     * @return void
     */
    public static function set(string $hash)
    {
        $_SESSION[self::PROFILE_KEY] = $hash;
    }
    /**
     * Returns MD5 hash from $_SESSION[PROFILE_KEY]
     *
     * @return string $hash : stored MD5 hash | default
     */
    public static function get()
    {
        return $_SESSION[self::PROFILE_KEY] ?? '';
    }
    /**
     * Verifies profile against stored
     *
     * @param array $config
     * @return bool TRUE if match | FALSE otherwise
     */
    public static function verify(array $config) : boolean
    {
        $stored = self::get();
        $actual = self::build($config);
        return ($stored === $actual);
    }
}
