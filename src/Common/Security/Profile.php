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
    const PROFILE_KEY = 'profile';
    const PROFILE_AUTH_UNABLE = 'ERROR: unable to authenticate';
    const USER_KEY = 'profile_username';
    const DEFAULT_USER = 'Unknown';
    public static $debug = FALSE;
    /**
     * Builds initial login profile + stores username
     *
     * @param array $config
     * @param string $username
     * @return void
     */
    public static function init(array $config, string $username = self::DEFAULT_USER) : void
    {
        $info = self::build($config);
        $info[self::USER_KEY] = $username;
        self::set($info);
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
     * Sets $_SESSION[PROFILE_KEY] to NULL
     *
     * @return void
     */
    public static function logout() : void
    {
        $_SESSION[self::PROFILE_KEY] = NULL;
    }
    /**
     * Saves login into in $_SESSION[PROFILE_KEY]
     *
     * @param array $info
     * @return void
     */
    public static function set(array $info)
    {
        $_SESSION[self::PROFILE_KEY] = $info;
    }
    /**
     * Returns into from $_SESSION[PROFILE_KEY]
     *
     * @return array $info : stored login info
     */
    public static function get() : array
    {
        return $_SESSION[self::PROFILE_KEY] ?? [];
    }
    /**
     * Verifies profile against stored
     *
     * @param array $config
     * @return bool TRUE if match | FALSE otherwise
     */
    public static function verify(array $config) : bool
    {
        $name   = $_SESSION[self::PROFILE_KEY][Profile::USER_KEY] ?? rand(1000,9999);
        $stored = self::get();
        $actual = self::build($config);
        $actual[self::USER_KEY] = $name;
        return ($stored === $actual);
    }
}
