<?php
namespace EasierThanWordPress\Transform;

/*
 * Unlikely\Import\Transform\Clean
 *
 * @description Uses Tidy extension to clean up HTML fragment
 * Only returns contents inside <body>*</body> if "bodyOnly" param is set
 *
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
class Clean extends Base
{
    const DESCRIPTION = 'Use Tidy extension to clean up HTML fragment';
    /**
     * Cleans up HTML using Tidy extension
     * If Tidy extension is not available, makes note in error log and return HTML untouched
     *
     * @param string $html : HTML string to be cleaned
     * @param array $params : ['bodyOnly' => : set TRUE (default) to only return content between <body>*</body>]
     * @return string $html : cleaned HTML
     */
    public function __invoke(string $html, array $params = []) : string
    {
        // if Tidy extension is available, perform cleanup
        if (class_exists('tidy')) {
            $tidy = new \tidy();
            $html = $tidy->repairString($html);
            $html = trim(str_replace("\n", '', $html));
            $bodyOnly = $params['bodyOnly'] ?? TRUE;
            if ($bodyOnly) {
                $matches = [];
                preg_match('!\<body\>(.+?)\<\/body\>!ims', $html, $matches);
                if (!empty($matches[1])) $html = $matches[1];
            }
        }
        return $html;
    }
}
