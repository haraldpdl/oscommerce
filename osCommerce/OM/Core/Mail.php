<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

use PHPMailer\PHPMailer\PHPMailer;

class Mail
{
    protected $phpmailer;

    public function __construct(string $to_email_address = null, string $to = null, string $from_email_address = null, string $from = null, string $subject = null, bool $auto_smtp_config = true)
    {
        $this->phpmailer = new PHPMailer();
        $this->phpmailer->CharSet = PHPMailer::CHARSET_UTF8;
        $this->phpmailer->XMailer = 'osCommerce';

        if ($auto_smtp_config === true) {
            $smtp_host = OSCOM::getConfig('smtp_host', 'OSCOM');

            if (!empty($smtp_host)) {
                $this->setSmtp($smtp_host, OSCOM::getConfig('smtp_port', 'OSCOM'), OSCOM::getConfig('smtp_secure_protocol', 'OSCOM'), OSCOM::getConfig('smtp_username', 'OSCOM'), OSCOM::getConfig('smtp_password', 'OSCOM'));
            }
        }

        if (!empty($to_email_address)) {
            $this->phpmailer->addAddress($to_email_address, $to);
        }

        if (!empty($from_email_address)) {
            $this->phpmailer->setFrom($from_email_address, $from);
        }

        if (!empty($subject)) {
            $this->phpmailer->Subject = $subject;
        }
    }

    public function addTo(string $email_address, string $name = null)
    {
        return $this->phpmailer->addAddress($email_address, $name);
    }

    public function setFrom(string $email_address, string $name = null)
    {
        return $this->phpmailer->setFrom($email_address, $name);
    }

    public function setReplyTo(string $email_address, string $name = null)
    {
        return $this->phpmailer->addReplyTo($email_address, $name);
    }

    public function addCC(string $email_address, string $name = null)
    {
        return $this->phpmailer->addCC($email_address, $name);
    }

    public function addBCC(string $email_address, string $name = null)
    {
        return $this->phpmailer->addBCC($email_address, $name);
    }

    public function clearTo()
    {
        $this->phpmailer->clearAllRecipients();
    }

    public function setSubject(string $subject)
    {
        $this->phpmailer->Subject = $subject;
    }

    public function setBody(string $text = null, string $html = null)
    {
        $this->phpmailer->isHTML(false);
        $this->phpmailer->Body = '';
        $this->phpmailer->AltBody = '';

        if (!empty($html)) {
            $this->phpmailer->isHTML(true);
            $this->phpmailer->Body = $html;
        }

        if (!empty($text)) {
            if (!empty($html)) {
                $this->phpmailer->AltBody = $text;
            } else {
                $this->phpmailer->Body = $text;
            }
        }
    }

    public function setBodyPlain(string $body)
    {
        if ($this->phpmailer->ContentType == PHPMailer::CONTENT_TYPE_TEXT_HTML) {
            $this->phpmailer->AltBody = $body;
        } else {
            $this->phpmailer->Body = $body;
        }
    }

    public function setBodyHTML(string $body)
    {
        if ($this->phpmailer->ContentType == PHPMailer::CONTENT_TYPE_PLAINTEXT) {
            $this->phpmailer->AltBody = $this->phpmailer->Body;
        }

        $this->phpmailer->isHTML(true);
        $this->phpmailer->Body = $body;
    }

    public function setContentTransferEncoding(string $encoding)
    {
        $this->phpmailer->Encoding = $encoding;
    }

    public function setCharset(string $charset)
    {
        $this->phpmailer->CharSet = $charset;
    }

    public function addHeader(string $key, string $value)
    {
        $this->phpmailer->addCustomHeader($key, $value);
    }

    public function clearHeaders()
    {
        $this->phpmailer->clearCustomHeaders();
    }

    public function addAttachment(string $file)
    {
        return $this->phpmailer->addAttachment($file);
    }

    public function addImage(string $file)
    {
        return $this->phpmailer->addAttachment($file, '', PHPMailer::ENCODING_BASE64, '', 'inline');
    }

    public function send()
    {
        return $this->phpmailer->send();
    }

    public function setSmtp(string $host, int $port, string $secure_protocol = null, string $username = null, string $password = null)
    {
        if (!empty($host)) {
            $this->phpmailer->IsSMTP();
            $this->phpmailer->Host = $host;
            $this->phpmailer->Port = $port;

            if (!empty($username)) {
                $this->phpmailer->SMTPAuth = true;
                $this->phpmailer->Username = $username;
                $this->phpmailer->Password = $password;
            }

            if (!empty($secure_protocol)) {
                $this->phpmailer->SMTPSecure = $secure_protocol;
            }
        }
    }

    public function getMailer()
    {
        return $this->phpmailer;
    }
}
