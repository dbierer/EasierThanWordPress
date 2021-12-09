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

class Validation extends Base
{
    const ERR_ALPHA = 'Non alpha and non-allowed characters found';
    const ERR_ALNUM = 'Non alpha-nunmeric and non-allowed characters found';
    const ERR_DIGIT = 'Text does not contain only numbers and non-allowed characters';
    const ERR_EMAIL = 'Invalid email address';
    const ERR_PHONE = 'Invalid phone number';
    const ERR_URL   = 'Invalid URL';
    const ERR_TOO_LONG = 'Text is too long';
    const ERR_TOO_SHORT = 'Text is too short';
    public static $errMessage = [];
    /**
     * Runs a set of validators against a string
     * If validation fails, the $errMessage property contains error descriptions
     *
     * @param string $text : string to validate
     * @param array $callbacks : array of callbacks to call: all callbacks need to return bool
     * @return bool : TRUE if validation succeeds, FALSE otherwise
     */
    public static function runValidators(string $text, array $callbacks)
    {
        $expected = 0;
        $actual   = 0;
        foreach ($callbacks as $method => $params) {
            $expected++;
            $actual += (int) self::$method($text, $params);
        }
        return ($expected === $actual);
    }
    /**
     * Returns error messages as a string
     *
     * @return string $messages
     */
    public static function getMessages() : string
    {
        return implode("\n", self::$errMessage);
    }
    /**
     * Validates alpha
     *
     * @param string $text
     * @param array $params : Allows list of characters in "allowed" parameter key
     * @return bool : TRUE if only alpha characters found, FALSE otherwise
     */
    public static function alpha(string $text, array $params = [])
    {
        $valid = TRUE;
        $allowed = $params['allowed'] ?? [];
        foreach ($allowed as $item)
            $text  = str_replace($item, '', $text);
        if (!ctype_alpha($text)) {
            self::$errMessage[] = self::ERR_ALPHA;
            $valid = FALSE;
        }
        return $valid;
    }
    /**
     * Validates digits
     *
     * @param string $text
     * @param array $params : Allows list of characters in "allowed" parameter key
     * @return bool : TRUE if only digits found, FALSE otherwise
     */
    public static function digits(string $text, array $params = [])
    {
        $valid = TRUE;
        $allowed = $params['allowed'] ?? [];
        foreach ($allowed as $item)
            $text  = str_replace($item, '', $text);
        if (!ctype_digit($text)) {
            self::$errMessage[] = self::ERR_DIGIT;
            $valid = FALSE;
        }
        return $valid;
    }
    /**
     * Validates alpha-numeric
     *
     * @param string $text
     * @param array $params : Allows list of characters in "allowed" parameter key
     * @return bool : TRUE if only alpha-numeric characters found, FALSE otherwise
     */
    public static function alnum(string $text, array $params = [])
    {
        $valid = TRUE;
        $allowed = $params['allowed'] ?? [];
        foreach ($allowed as $item)
            $text  = str_replace($item, '', $text);
        if (!ctype_alnum($text)) {
            self::$errMessage[] = self::ERR_ALNUM;
            $valid = FALSE;
        }
        return $valid;
    }
    /**
     * Validates phone number
     *
     * @param string $text
     * @param array $params : Allows list of characters in "allowed" parameter key
     * @return bool : TRUE if valid phone number, FALSE otherwise
     */
    public static function phone(string $text, array $params = [])
    {
        return self::digits($text, $params);
    }
    /**
     * Validates email address
     *
     * @param string $text
     * @param array $params : not used
     * @return bool : TRUE if valid email, FALSE otherwise
     */
    public static function email(string $text, array $params = [])
    {
        $valid = TRUE;
        if (!filter_var($text, FILTER_VALIDATE_EMAIL)) {
            self::$errMessage[] = self::ERR_EMAIL;
            $valid = FALSE;
        }
        return $valid;
    }
    /**
     * Validates URL
     *
     * @param string $text
     * @param array $params : not used
     * @return bool : TRUE if valid URL found, FALSE otherwise
     */
    public static function url(string $text, array $params = [])
    {
        $valid = TRUE;
        if (!filter_var($text, FILTER_VALIDATE_URL)) {
            self::$errMessage[] = self::ERR_URL;
            $valid = FALSE;
        }
        return $valid;
    }
    /**
     * Checks if string is too long
     *
     * @param string $text
     * @param array $params : "size" : string must be < or = to this value
     * @return bool : TRUE if string is not too long, FALSE otherwise
     */
    public static function notTooLong(string $text, array $params = [])
    {
        $valid = TRUE;
        $size  = (isset($params['size']))
               ? (int) $params['size']
               : strlen($text);
        if (strlen($text) > $size) {
            self::$errMessage[] = self::ERR_TOO_LONG;
            $valid = FALSE;
        }
        return $valid;
    }
    /**
     * Checks if string is too short
     *
     * @param string $text
     * @param array $params : "size" : string must be > or = to this value
     * @return bool : TRUE if string is not too short, FALSE otherwise
     */
    public static function notTooShort(string $text, array $params = [])
    {
        $valid = TRUE;
        $size  = (isset($params['size']))
               ? (int) $params['size']
               : strlen($text);
        if (strlen($text) < $size) {
            self::$errMessage[] = self::ERR_TOO_SHORT;
            $valid = FALSE;
        }
        return $valid;
    }
}
