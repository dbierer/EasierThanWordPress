<?php
namespace FileCMS\Common\View;
/**
 *
 * @title  : FileCMS\Common\Table
 * @date   : 08 Sep 2022
 * @author : doug@unlikelysource.com
 * @license : BSD (see below)
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

class Table
{
    /**
     * Returns CSS table with specified columns
     *
     * @param array $data      : multi-dimensional array of data
     * @param array $headers   : desired headers
     * @param array $callbacks : array of callable; used for special column formatting
     * @return string $html    : HTML table
     */
    public static function render_as_div(array $data, array $headers, array $callbacks = []) : string
    {
        $html = '<div class="row">';
        foreach ($headers as $item) $html .= '<div class="col"><b>' . ucfirst($item) . '</b></div>';
        $html .= '</div>' . PHP_EOL;
        $prev = '';
        foreach ($data as $key => $row) {
            $html .= '<div class="row">';
            foreach ($headers as $idx => $item) {
                $html .= '<div class="col">';
                if (empty($callbacks[$idx])) {
                    $html .= $row[$item] ?? '--';
                } else {
                    $html .= $callbacks[$idx]($row[$item]);
                }
                $html .= '</div>' . PHP_EOL;
            }
            $html .= '</div>' . PHP_EOL;
        }
        return $html;
    }
    /**
     * Returns HTML table with specified columns
     *
     * @param array $css_class : table, tr, td, th classes
     * @param array $data      : multi-dimensional array of data
     * @param array $headers   : desired headers
     * @param array $callbacks : array of callable; used for special column formatting
     * @return string $html    : HTML table
     */
    public static function render_table(array $data, array $headers, array $callbacks = [], array $css_class = []) : string
    {
        $getTag = function ($type, $css_class) {
            $tag = '<' . $type;
            $end = (!empty($css_class[$type]))
                 ? ' class="' . $css_class[$type] . '">'
                 : '>';
            return $tag . $end;
        };
        $html = $getTag('table', $css_class);
        $html .= $getTag('tr', $css_class);
        foreach ($headers as $item) {
            $html .= $getTag('th', $css_class);
            $html .= ucfirst($item);
            $html .= '</th>';
        }
        $html .= '</tr>' . PHP_EOL;
        foreach ($data as $key => $row) {
            $html .= $getTag('tr', $css_class);
            foreach ($headers as $idx => $item) {
                $html .= $getTag('td', $css_class);
                if (empty($callbacks[$idx])) {
                    $html .= $row[$item] ?? '--';
                } else {
                    $html .= $callbacks[$idx]($row[$item]);
                }
                $html .= '</td>';
            }
            $html .= '</tr>' . PHP_EOL;
        }
        $html .= '</table>' . PHP_EOL;
        return $html;
    }
}
