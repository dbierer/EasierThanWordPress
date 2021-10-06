<?php
namespace SimpleHtml\Common\File;

/*
 * Handles generic files uploads
 * Oriented towards graphics file uploads for CKEditor
 *
 * Author: doug@unlikelysource.com
 * License: BSD
 * Revision Date: 2021-10-06
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
use SimpleHtml\Common\Generic\Messages;
use InvalidArgumentException;
class Upload
{

    const UPLOAD_ALERT            = 'alert("%s";';
    const UPLOAD_SUCCESS          = 'SUCCESS: uploaded %s (%d bytes)';
    const UPLOAD_ERROR_NO_GO      = 'ERROR: homey don\'t go there!';
    const UPLOAD_ERROR_IMAGE      = 'ERROR: unable to upload image file';
    const UPLOAD_ERROR_TOKEN      = 'ERROR: token error';
    const UPLOAD_ERROR_UPLOAD     = 'ERROR: invalid uploaded file';
    const UPLOAD_ERROR_TYPE       = 'ERROR: invalid file type: %s';
    const UPLOAD_ERROR_SOURCE     = 'ERROR: invalid file source';
    const UPLOAD_ERROR_IMAGE_SIZE = 'ERROR: existing width x height: %d  x %d. Allowed width x weight: %d x %d';
    const UPLOAD_ERROR_FILE_SIZE  = 'ERROR: maximum file size: %s bytes';
    const UPLOAD_ERROR_MISSING    = 'ERROR: configuration is missing the "UPLOADS" key';
    const UPLOAD_DEFAULT_URL      = '/images';
    const UPLOAD_DEFAULT_EXT      = ['jpg','jpeg','png','gif','bmp'];
    const UPLOAD_DEFAULT_TYPES    = ['image/'];
    const UPLOAD_DEFAULT_WIDTH    = 1000;
    const UPLOAD_DEFAULT_HEIGHT   = 1000;
    const UPLOAD_DEFAULT_SIZE     = 3000000;
    public $allowed_hosts         = ['localhost' => 'localhost', 'your.website.com' => 'your.website.com'];
    public $errors                = [];
    public $config                = [];

    /**
     * @param array $config : file upload information; looks for a key 'UPLOADS'
     */
    public function __construct(array $config)
    {
        $this->config = $config['UPLOADS'] ?? [];
        if (empty($this->config))
            throw new InvalidArgumentException(self::UPLOAD_ERROR_MISSING);
    }

    /**
     * Handles file upload
     *
     * @param string $field : name of the field in $_FILES containing uploaded file info
     * @return array $response : array containing information on the upload
     */
    public function handle(string $field)
    {
        $upload_dir  = $this->config['upload_dir'] ?? '/tmp';
        $fn          = $_FILES[$field]['name'] ?? '';
        $tmp_file    = $_FILES[$field]['tmp_name'] ?? '';
        $url         = $this->config['url'] ?? self::UPLOAD_DEFAULT_URL;
        $allowed_ext = $this->config['allowed_ext'] ?? self::UPLOAD_DEFAULT_EXT;
        $message     = Messages::getInstance();
        $response = [
            'uploaded' => 1,
            'fileName' => $fn,
            'url'      => $url . '/' . $fn
        ];
        if (empty($fn) || empty($tmp_file)) {
            $this->errors[] = self::UPLOAD_ERROR_UPLOAD;
            return $this->getErrorResponse($response);
        }
        // sanitize filename
        $fn  = strip_tags(basename($fn));
        $ext = strtolower(pathinfo($fn,  PATHINFO_EXTENSION));
        // check for allowed file types
        if (empty($ext) || !in_array($ext, $allowed_ext, TRUE)) {
            $this->errors[] = sprintf(self::UPLOAD_ERROR_TYPE, htmlspecialchars($ext));
            return $this->getErrorResponse($response);
        }
        // verify image size and type
        $restrict = $this->config['restrict_size'] ?? TRUE;
        if ($restrict && !$this->checkImageSize($tmp_file)) {
            return $this->getErrorResponse($response);
        }
        if (!is_uploaded_file($tmp_file)) {
            $this->errors[] = self::UPLOAD_ERROR_UPLOAD;
            return $this->getErrorResponse($response);
        }
        // looks good ... move the file
        $uploadpath = str_replace('//', '/', $upload_dir . '/' . $fn);
        if (move_uploaded_file($_FILES['upload']['tmp_name'], $uploadpath)) {
            $url = str_replace('//', '/', $url . '/' . $fn);
            $message->addMessage(sprintf(self::UPLOAD_SUCCESS, $fn, filesize($tmp_file)));
        } else {
            $this->errors[] = self::UPLOAD_ERROR_IMAGE;
            $response = $this->getErrorResponse($response);
        }
        return $response;
    }
    /**
     * Checks image for size
     *
     * @param string $tmp_file : filename of the image (from $_FILES['tmp_name'])
     * @return bool TRUE if image matches restrictions; FALSE otherwise
     */
    public function checkImageSize(string $tmp_file)
    {
        $info = getimagesize($tmp_file);
        $valid  = 4;
        $actual = 0;
        $width  = $info[0] ?? PHP_INT_MAX;
        $height = $info[1] ?? PHP_INT_MAX;
        $type   = $info['mime'] ?? 'unknown';
        $max_w  = $this->config['img_width'] ?? self::UPLOAD_DEFAULT_WIDTH;
        $max_h  = $this->config['img_height'] ?? self::UPLOAD_DEFAULT_HEIGHT;
        $max_sz = $this->config['img_size'] ?? self::UPLOAD_DEFAULT_WIDTH;
        if (!empty($info) && is_array($info) && count($info) == 6) {
            $types  = $this->config['allowed_types'] ?? self::UPLOAD_DEFAULT_TYPES;
            $actual += (int) $width <= $max_w;
            $actual += (int) $height <= $max_h;
            $actual += filesize($tmp_file) <= $max_sz;
            foreach ($types as $item) {
                if (stripos($type, $item) === 0) {
                    $actual++;
                    break;
                }
            }
        }
        $ok = ($valid === $actual);
        if (!$ok) {
            $this->errors[] = sprintf(self::UPLOAD_ERROR_IMAGE_SIZE, $width, $height, $max_w, $max_h);
            $this->errors[] = sprintf(self::UPLOAD_ERROR_FILE_SIZE, $max_sz);
        }
        return $ok;
    }
    /**
     * Returns an error response
     *
     * @param array $response  : response array in its current state
     * @return array $response : with error conditions set
     */
    public function getErrorResponse(array $response = [])
    {
        $response['uploaded'] = 0;
        $response['error'] = trim(implode("\n", $this->errors));
        return $response;
    }
}
