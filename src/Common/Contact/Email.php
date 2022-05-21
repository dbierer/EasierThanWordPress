<?php
namespace FileCMS\Common\Contact;
/*
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

/**
 * Sends email using PHPMailer
 */
use PHPMailer\PHPMailer\PHPMailer;
class Email
{
    const BODY_PATTERN = '%-20s : %s' . "\n";
    const DEFAULT_SUBJECT = 'Customer Request';
    const DEFAULT_SUCCESS = 'SUCCESS: email sent';
    const DEFAULT_ERROR   = 'ERROR: unable to send email';
    const DEBUG_MSG       = 'DEBUG mode enabled: mail not sent';
    public static $error  = FALSE;
    public static $phpMailer = NULL;
    /**
     * @param array $config : from /src/config/config.php
     * @param array $inputs : filtered and validated $_POST data
     * @param string $body  : message body is passed back by reference
     * @param bool   $debug   : set TRUE to set debug mode: doesn't send mail
     * @return string $msg  : any error or other messages
     */
    public static function processPost( array $config,
                                        array $inputs,
                                        string &$body,
                                        bool $debug = FALSE)
    {
        $msg = $config['COMPANY_EMAIL']['ERROR'] ?? self::DEFAULT_ERROR;
        // sanitize "from" email
        $email = $inputs['email'] ?? '';
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        // $inputs = self::filter($table, $post, $config);
        $hashKey   = $config['CAPTCHA']['sess_hash_key'] ?? 'hash';
        $phraseKey = $config['CAPTCHA']['input_tag_name'] ?? 'phrase';
        if (!empty($inputs)&& !empty($email)) {
            $body    = "\n";
            foreach ($inputs as $key => $value)
                $body .= sprintf(self::BODY_PATTERN, ucfirst($key), $value);
            $subject = $inputs['subject']
                     ?? $config['COMPANY_EMAIL']['default_subject']
                     ?? self::DEFAULT_SUBJECT;
            $msg = Email::confirmAndSend($email, $config, $subject, $body, $debug);
        }
        return $msg;
    }
    /**
     * Sends email using PHPMailer
     *
     * @param string $from    : sender's email address
     * @param array $config   : primary config file
     * @param string $subject : email subject line
     * @param string $body    : email message
     * @param bool   $debug   : set TRUE to set debug mode: doesn't send mail
     * @return string $msg    : success/failure message
     */
    public static function confirmAndSend(  string $from,
                                            array $config,
                                            string $subject,
                                            string $body,
                                            bool $debug = FALSE)
    {
        $msg       = $config['COMPANY_EMAIL']['ERROR'] ?? self::DEFAULT_ERROR;
        $phraseKey = $config['CAPTCHA']['input_tag_name'] ?? 'phrase';
        $hashKey   = $config['CAPTCHA']['sess_hash_key'] ?? 'hash';
        $phrase    = $_REQUEST[$phraseKey] ?? '';
        $hash      = $_SESSION[$hashKey] ?? 'UNKNOWN';
        $to        = $config['COMPANY_EMAIL']['to'];
        $cc        = $config['COMPANY_EMAIL']['cc'] ?? '';
        $bcc       = $config['COMPANY_EMAIL']['bcc'] ?? '';
        $phpmailerConfig = $config['COMPANY_EMAIL']['phpmailer'];
        if (password_verify($phrase, $hash)) {
            // validate email
            if (PHPMailer::validateAddress($from)) {
                // send request
                $msg = self::trustedSend($config, $to, $from, $subject, $body, $cc, $bcc, $debug);
            } else {
                $msg = $config['COMPANY_EMAIL']['ERROR'] ?? self::DEFAULT_ERROR;
                error_log(basename(__FILE__) . ': email does not verify');
            }
        } else {
            $msg = $config['COMPANY_EMAIL']['ERROR'] ?? self::DEFAULT_ERROR;
            error_log(basename(__FILE__) . ': CAPTCHA does not verify');
        }
        return $msg;
    }
    /**
     * Sends email using PHPMailer
     * Trusts that all validation is done
     *
     * @param array $config   : primary config file
     * @param string $to      : recipient's email address
     * @param string $from    : sender's email address
     * @param string $subject : email subject line
     * @param string $body    : email message
     * @param mixed  $cc      : optional CC list
     * @param mixed  $bcc     : optional BCC list
     * @param bool   $debug   : set TRUE to set debug mode: doesn't send mail
     * @return string $msg    : success/failure message
     */
    public static function trustedSend( array $config,
                                        string $to,
                                        string $from,
                                        string $subject,
                                        string $body,
                                        $cc = '',
                                        $bcc = '',
                                        bool $debug = FALSE)
    {
        $msg = $config['COMPANY_EMAIL']['ERROR'] ?? self::DEFAULT_ERROR;
        $phpmailerConfig = $config['COMPANY_EMAIL']['phpmailer'];
        // send request
        try {
            // set up SMTP
            $mail = new PHPMailer();
            if (isset($phpmailerConfig['smtp'])) {
                $mail->IsSMTP();
                $mail->Host       = $phpmailerConfig['smtp_host'] ?? '';
                $mail->Port       = $phpmailerConfig['smtp_port'];
                $mail->SMTPAuth   = $phpmailerConfig['smtp_auth'];
                $mail->Username   = $phpmailerConfig['smtp_username'] ?? '';
                $mail->Password   = $phpmailerConfig['smtp_password'] ?? '';
                $mail->SMTPSecure = $phpmailerConfig['smtp_secure'] ?? 'tls';
            }
            // set up mail obj
            $mail->setFrom($from);
            self::queueOutbound($to, 'to', $mail);
            self::queueOutbound($cc, 'cc', $mail);
            self::queueOutbound($bcc, 'bcc', $mail);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            if ($debug === TRUE) {
                self::$phpMailer = $mail;
                $msg = self::DEBUG_MSG;
            } else {
                //send the message, check for errors
                if ($mail->send()) {
                    $msg = $config['COMPANY_EMAIL']['SUCCESS'] ?? self::DEFAULT_SUCCESS;
                } else {
                    $msg = $config['COMPANY_EMAIL']['ERROR'] ?? self::DEFAULT_ERROR;
                    self::$error = TRUE;
                }
            }
        } catch (\Exception $e) {
            self::$error = TRUE;
            $msg = $config['COMPANY_EMAIL']['ERROR'] ?? self::DEFAULT_ERROR;
            error_log(__METHOD__ . ':' . $e->getMessage());
        }
        return $msg;
    }
    /**
     * If $data is an array, calls $method for each element
     *
     * @param mixed $data : data to be added to queued items
     * @param string $method : to|cc|bcc
     * @param PHPMailer $mail : PHPMailer instance
     * @return void
     */
    public static function queueOutbound($data, string $method, PHPMailer $mail) : void
    {
        if (!empty($data)) {
            switch ($method) {
                case 'cc' :
                    $method = 'addCC';
                    break;
                case 'bcc' :
                    $method = 'addBCC';
                    break;
                case 'to' :
                default :
                    $method = 'addAddress';
                    break;
            }
            if (is_array($data)) {
                foreach ($data as $item)
                    $mail->$method($item);
            } else {
                $mail->$method($data);
            }
        }
    }
    /**
     * Uses PHPMailer to validate email address
     *
     * @param string $email : email address to validate
     * @return bool
     */
    public static function validateEmail(string $email)
    {
        return PHPMailer::validateAddress($email);
    }
}
