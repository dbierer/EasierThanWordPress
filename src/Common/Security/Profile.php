<?php
namespace FileCMS\Common\Security;
/*
 * Author: doug@unlikelysource.com
 * License: BSD
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

class Profile
{

    const PROFILE_AUTH_UNABLE = 'ERROR: unable to authenticate';
    const DEFAULT_AUTH_DIR    = BASE_DIR . '/logs';
    const DEFAULT_AUTH_PREFIX = 'AUTH_';
    const AUTH_FILE_TTL       = 86400;     // # seconds old auth files can remain (86400 secs == 24 hours)

    public static $debug   = FALSE;
    public static $authDir = '';

    /**
     * Creates unique auth file name based on profile
     *
     * @param array $config
     * @param array $info : profile info
     * @return string $authFilename
     */
    public static function getAuthFileName(array $config, array $info)
    {
        self::$authDir = $config['AUTH_DIR'] ?? self::DEFAULT_AUTH_DIR;
        $fn = self::DEFAULT_AUTH_PREFIX . md5(implode('', $info));
        return str_replace('//', '/', self::$authDir . '/' . $fn);
    }
    /**
     * Builds initial login profile + sets up auth file
     *
     * @param array $config
     * @return void
     */
    public static function init(array $config) : void
    {
        $info = self::build($config);
        $fn   = self::getAuthFileName($config, $info);
        file_put_contents($fn, json_encode($info, JSON_PRETTY_PRINT));
    }
    /**
     * Pulls $_SERVER keys into array
     *
     * @param array $config
     * @return string $hash : md5() hash
     */
    public static function build(array $config)
    {
        $info = [];
        $keys = $config['SUPER']['profile'] ?? [];
        if (empty($keys)) {
            $info['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'] ?? date('Y-m-d');
        } else {
            foreach ($keys as $idx)
                $info[$idx] = $_SERVER[$idx] ?? date('Y-m-d');
        }
        return $info;
    }
    /**
     * Removes auth file
     *
     * @param array $config
     * @return void
     */
    public static function logout(array $config) : void
    {
        $info = self::build($config);
        $fn   = self::getAuthFileName($config, $info);
        if (file_exists($fn)) unlink($fn);
        // clean out old auth files
        $path = str_replace('//', '/', self::$authDir . '/' . self::DEFAULT_AUTH_PREFIX . '*');
        $iter = glob($path);
        $now = time();
        $expired = $now - self::AUTH_FILE_TTL;
        foreach ($iter as $name) {
            // find files older than 24 hours
            if (is_file($name) && filectime($name) < $expired) {
                unlink($name);
            }
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }
    /**
     * Verifies profile against stored
     *
     * @param array $config
     * @return bool TRUE if match | FALSE otherwise
     */
    public static function verify(array $config) : bool
    {
        $info = self::build($config);
        $fn   = self::getAuthFileName($config, $info);
        $ok = file_exists($fn);
        if (!$ok)
            error_log(__METHOD__ . ':AUTH FILE:' . $fn . ':INFO:' . var_export($info, TRUE));
        return $ok;
    }
}
