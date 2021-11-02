<?php
namespace FileCMS\Transform;

/*
 * FileCMS\Transform\CleanAttributes
 *
 * @description Removes "\n" in front of listed attributes
 * @author doug@unlikelysource.com
 * @date 2021-10-04
 * Copyright 2021 unlikelysource.com
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
use FileCMS\Common\Transform\Base;
class CleanAttributes extends Base
{
    const DESCRIPTION = 'Remove "\n" in front of listed attributes';
    public $attributes = [];
    /**
     * Removes "\n" in front of listed attributes
     *
     * @param string $html : HTML string to be cleaned
     * @param array $params : ['attributes' => [array,of,attributes,to,remove]]
     * @return string $html : HTML with "\n" removed from in front of attribute
     */
    public function __invoke(string $html, array $params = []) : string
    {
        $this->attributes  = $params['attributes'] ?? [];
        if (empty($this->attributes)) return $html;
        foreach ($this->attributes as $attrib) {
            $search = "\n" . $attrib . '=';
            $replace = ' ' . $attrib . '=';
            $html = str_replace($search, $replace, $html);
        }
        $html = str_replace('  ', ' ', $html);
        return $html;
    }
}
