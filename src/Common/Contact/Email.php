<?php
namespace FileCMS\Common\Contact;

use PHPMailer\PHPMailer\PHPMailer;
class Email extends Base
{
    const BODY_PATTERN = '%-20s : %s' . "\n";
    const DEFAULT_SUBJECT = 'Customer Request';
    const DEFAULT_SUCCESS = 'SUCCESS: email sent';
    const DEFAULT_ERROR   = 'ERROR: unable to send email';
    /**
     * @param array $config : from /src/config/config.php
     * @param array $post   : $_POST
     * @param string $body  : message body is passed back by reference
     * @param string $table : database table for filters
     * @return string $msg  : any error or other messages
     */
    public static function processPost(array $config, array $post, string &$body, string $table = Storage::DEFAULT_TABLE)
    {
        $msg = '';
        // sanitize email
        $email = $_POST['email'] ?? '';
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $inputs = self::filter($table, $post, $config);
        $hashKey   = $config['CAPTCHA']['sess_hash_key'] ?? 'hash';
        $phraseKey = $config['CAPTCHA']['input_tag_name'] ?? 'phrase';
        if ($_POST) {
            $msg = $config['COMPANY_EMAIL']['ERROR'] ?? self::DEFAULT_ERROR;
            if (!empty($email)) {
                $body    = "\n";
                foreach ($inputs as $key => $value)
                    $body .= sprintf(self::BODY_PATTERN, ucfirst($key), $value);
                $subject = $inputs['subject'] ?? self::DEFAULT_SUBJECT;
                $msg = Email::confirmAndSend($email, $config, $subject, $body);
            }
        }
        return $msg;
    }
    public static function confirmAndSend(
        string $email,
        array $config,
        string $subject,
        string $body)
    {
        $msg       = $config['COMPANY_EMAIL']['ERROR'] ?? self::DEFAULT_ERROR;
        $phraseKey = $config['captcha']['input_tag_name'] ?? 'phrase';
        $hashKey   = $config['captcha']['sess_hash_key'] ?? 'hash';
        $phrase    = $_REQUEST[$phraseKey] ?? '';
        $hash      = $_SESSION[$hashKey] ?? 'UNKNOWN';
        $to        = $config['COMPANY_EMAIL']['to'];
        $cc        = $config['COMPANY_EMAIL']['cc'] ?? '';
        $bcc       = $config['COMPANY_EMAIL']['bcc'] ?? '';
        $from      = $config['COMPANY_EMAIL']['from'];
        $phpmailerConfig = $config['COMPANY_EMAIL']['phpmailer'];
        if (password_verify($phrase, $hash)) {
            // validate email
            if (PHPMailer::validateAddress($email)) {
                // send request for 30 day trial
                try {
                    // set up SMTP
                    $mail = new PHPMailer();
                    if ($phpmailerConfig['smtp']) {
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
                    $mail->addAddress($to);
                    if ($cc) $mail->addCC($cc);
                    if ($bcc) $mail->addBCC($bcc);
                    $mail->Subject = $subject;
                    $mail->Body    = $body;
                    //send the message, check for errors
                    if ($mail->send()) {
                        $msg = $config['COMPANY_EMAIL']['SUCCESS'] ?? self::DEFAULT_SUCCESS;
                    } else {
                        $msg = $config['COMPANY_EMAIL']['ERROR'] ?? self::DEFAULT_ERROR;
                    }
                } catch (\Exception $e) {
                    $msg = $config['COMPANY_EMAIL']['ERROR'] ?? self::DEFAULT_ERROR;
                    error_log(__METHOD__ . ':' . $e->getMessage());
                }
            } else {
                error_log(basename(__FILE__) . ': email does not verify');
            }
        } else {
            error_log(basename(__FILE__) . ': CAPTCHA does not verify');
        }
        return $msg;
    }
}
