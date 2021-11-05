<?php
namespace FileCMS\Transform;

/*
 * FileCMS\Transform\Clean
 *
 * @description converts HTML <table><tr><td>|<th> to <div class="row"><div class="col-xxx">
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
use InvalidArgumentException;
use FileCMS\Common\Transform\Base;
class TableToDiv extends Base
{
    const DESCRIPTION = 'Convert HTML &lt;table&gt;&lt;tr&gt;&lt;td&gt;|&lt;th&gt; to &lt;div class="row"&gt;&lt;div class="col-xxx"&gt;';
    const DEFAULT_TR = 'row';
    const DEFAULT_TD = 'col';
    const DEFAULT_TH = 'col bold';
    public $tr = '';  // row class (default == "row")
    public $td = '';  // column class (default == "col")
    public $th = '';  // header column class (default == "col bold")
    /**
     * Converts HTML <table><tr><td>|<th> to <div class="row"><div class="col">
     *
     * @param string $html : HTML string to be cleaned
     * @param array $params : ['td'   => : td class (default: "col")
     *                         'th'   => : td class (default: "col bold")
     *                         'tr'   => : row class (default: "row")]
     * @return string $html : HTML with <table><tr><td>|<th> conversions done
     */
    public function __invoke(string $html, array $params = []) : string
    {
        $this->init($params);
        return $this->convert($html);
    }
    /**
     * Initializes properties
     *
     * @param array $params : ['tr'   => : row class
     *                         'td'   => : column class
     *                         'th' =>   : header column class
     * @return void
     * @thtrs InvalidArgumentException
     */
    public function init(array $params) : void
    {
        $this->td = $params['td'] ?? static::DEFAULT_TD;
        $this->tr = $params['tr'] ?? static::DEFAULT_TR;
        $this->th = $params['th'] ?? static::DEFAULT_TH;
    }
    /**
     * Performs conversion:
     * -- removes table tags
     * -- converts <tr> into <div class="$this->tr">
     * -- converts <td> into <div class="$this->td">
     * -- converts <th> into <div class="$this->th">
     *
     * @param string $html : HTML string to be cleaned
     * @return string $html : HTML with <tr></tr> conversions done
     */
    public function convert(string $html) : string
    {
        $html = $this->removeTableTags($html);
        $html = $this->convertRow($html);
        $html = $this->convertCol($html);
        return $html;
    }
    /**
     * Removes "<table><tbody><thead>" and related closing tags
     *
     * @param string $html : HTML string to be cleaned
     * @return string $html : HTML with table tags removed
     */
    public function removeTableTags(string $html) : string
    {
        $search = ['!<table.*?>!i','!<thead.*?>!i','!<tbody.*?>!i'];
        $html = preg_replace($search, '', $html);
        $html = str_ireplace(['</table>','</thead>','</tbody>'], '', $html);
        return $html;
    }
    /**
     * Convert <tr> => <div class="row">
     *
     * @param string $html : HTML string to be cleaned
     * @return string $html : HTML with <tr></tr> conversions done
     */
    public function convertRow(string $html) : string
    {
        $search = '!<tr.*?>!i';
        $html = preg_replace($search, '<div class="' . $this->tr . '">', $html);
        $html = str_ireplace('</tr>', '</div>', $html);
        return $html;
    }
    /**
     * Converts <td> => <div class="col-XXX"> and <th> => <div class="col-XXX"><b>
     * Converts </td> => </div> and </th> => </b></div>
     *
     * @param string $html : HTML string to be cleaned
     * @return string $html : HTML with <tr></tr> conversions done
     */
    public function convertCol(string $html) : string
    {
        $search = '!<td.*?>!i';
        $html = preg_replace($search, '<div class="' . $this->td . '">', $html);
        $html = str_ireplace('</td>', '</div>', $html);
        $search = '!<th.*?>!i';
        $html = preg_replace($search, '<div class="' . $this->th . '">', $html);
        $html = str_ireplace('</th>', '</div>', $html);
        return $html;
    }
}
