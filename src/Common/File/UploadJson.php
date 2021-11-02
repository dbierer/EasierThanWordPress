<?php
namespace FileCMS\Common\File;

/*
 * FileCMS\Common\File\UploadJson
 *
 * @description imports contents from "trusted" website(s)
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

use FileCMS\Common\Generic\Messages;
class UploadJson
{
    const ERR_UPLOAD = 'ERROR: unable to upload file';
    const ERR_NO_DATA = 'ERROR: no data';
    const ERR_CONTENTS = 'ERROR: file has no contents';
    const ERR_NO_UPLOADED = 'ERROR: no uploaded file found';
    /**
     * Uploads a JSON file
     * Contents must be in JSON format
     *
     * @param string $field : name of the field in $_FILES that contains import info
     * @param array $info   : $_FILES
     * @param FileCMS\Common\Generic\Messages instance
     * @return array $data : JSON decoded data as PHP array; returns empty array + message if failed
     */
    public static function upload(string $field, array $info, Messages $message)
    {
        $data = [];
        // is there an upload error?
        if ($info[$field]['error'] !== UPLOAD_ERR_OK) {
            $message->addMessage(self::ERR_UPLOAD);
        } else {
            // is this an uploaded file?
            if (!is_uploaded_file($info[$field]['tmp_name'])) {
                $message->addMessage(self::ERR_NO_UPLOADED);
            } else {
                // ok, go ahead and load the file
                $text = file_get_contents($info[$field]['tmp_name']);
                if (empty($text)) {
                    $message->addMessage(self::ERR_CONTENTS);
                } else {
                    $data = json_decode($text, TRUE);
                    if ($data === NULL) {
                        $message->addMessage(json_last_error_msg());
                        $data = [];
                    } elseif ($data === FALSE) {
                        $message=>addMessage(self::ERR_NO_DATA);
                        $data = [];
                    }
                }
            }
        }
        return $data;
    }
}
