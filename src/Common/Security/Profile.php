<?php
namespace EasierThanWordPress\Common\Security;
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
    public static function verify(array $config) : bool
    {
        $stored = self::get();
        $actual = self::build($config);
        return ($stored === $actual);
    }
}
