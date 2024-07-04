<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class Smtp {

    const NL = "\r\n";

    private $server, $port, $user, $password, $localhost, $connection;
    private $connectTimeout = 30;
    private $responseTimeout = 20;
    private $fromMail, $fromName, $toMail, $toName, $subject, $message, $log = array();
    private $headers = array();
    private $starttls = false;

    public function __construct($server, $user, $password, $port = 25, $localhost = 'localhost')
    {
        $this->server = trim($server);
        $this->user = trim($user);
        $this->password = $password;
        $this->port = intval(trim($port));
        $this->localhost = $localhost;

        $protocolPosition = strpos($this->server, '//');

        if ($protocolPosition === false)
        {
            if (($this->port == 465) or ($this->port == 443)){
                $this->server = 'ssl://'.$this->server;
            }
            if (($this->port == 587)){
                $this->server = 'tls://'.$this->server;
            }
        } elseif ($protocolPosition === 0) {
            if($this->port == 587) {
                $this->starttls = true;
            }
            $this->server = trim($this->server, '/');
        }


        $this->setHeader('MIME-Version', '1.0');
        $this->setHeader('Content-type', 'text/html; charset=utf-8');
        $this->setHeader('X-Mailer', 'PHP v' . phpversion());
        $this->setHeader('Content-Transfer-Encoding', '8bit');
    }

    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    private function headersToString()
    {
        $headers = '';
        foreach ($this->headers as $key => $value) {
            $headers .= $key . ': ' . $value . self::NL;
        }

        return $headers;
    }

    private function getResponse()
    {
        $response = '';
        while (($line = fgets($this->connection, 1024)) != false) {
            $response .= trim($line) . "\n";
            if (substr($line, 3, 1) == ' ') {
                break;
            }
        }
        return trim($response);
    }

    private function sendCmd($cmd)
    {
        $this->log($cmd, $cmd);
        fputs($this->connection, $cmd . self::NL);
        stream_set_timeout($this->connection, $this->responseTimeout);

        return $this->getResponse();
    }

    private function log($key, $value = '')
    {
        $this->log[$key] = $value;
    }

    public function logGetValue($key)
    {
        return isset($this->log[$key]) ? $this->log[$key] : null;
    }

    public function setTo($mail, $name = null)
    {
        $this->toMail = $mail;
        $this->toName = $name;
    }

    public function setFrom($mail, $name = null)
    {
        $this->fromMail = $mail;
        $this->fromName = $name;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    private function address($mail, $name = null)
    {
        if($name !== null) {
            $name = '"=?utf-8?b?' . base64_encode(trim($name)) . '?="';
        }

        return trim("$name <$mail>");
    }

    private function connect()
    {
        $context = stream_context_create(array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false
            )
        ));

        $this->connection = stream_socket_client($this->server . ':' . $this->port, $errno, $errstr, $this->connectTimeout, STREAM_CLIENT_CONNECT, $context);

        //$this->connection = fsockopen($this->server, $this->port, $errno, $errstr, $this->connectTimeout);

        if (empty($this->connection)) {
            return false;
        }

        return true;
    }

    private function disconnect()
    {
        fclose($this->connection);
    }

    public function send()
    {
        if (!$this->connect()) {
            return false;
        }

        $this->log('connection', $this->getResponse());

        $this->log('helo', $this->sendCmd("EHLO {$this->localhost}"));

        if($this->starttls) {
            $this->log('STARTTLS', $this->sendCmd('STARTTLS'));

            $cryptoType = STREAM_CRYPTO_METHOD_TLS_CLIENT;

            if (defined('STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT') && defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                $cryptoType |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            }

            stream_socket_enable_crypto($this->connection, true, $cryptoType);

            $this->log('helo', $this->sendCmd("EHLO {$this->localhost}"));
        }

        $this->log('auth', $this->sendCmd('AUTH LOGIN'));
        $this->log('user', $this->sendCmd(base64_encode($this->user)));
        $this->log('password', $this->sendCmd(base64_encode($this->password)));

        if ((int)$this->logGetValue('password') != 235 && stripos($this->logGetValue('auth'), 'Error: authentication not enabled') === false) {
            return false;
            trigger_error('SMTP auth error ' . $this->logGetValue('password'));
        }
        $this->log('from', $this->sendCmd("MAIL FROM:<{$this->fromMail}>"));
        $this->log('to', $this->sendCmd("RCPT TO:<{$this->toMail}>"));

        if($this->logGetValue('to') === '550 non-local recipient verification failed') {
            return true;
        }

        $this->log('data', $this->sendCmd('DATA'));

        $this->setHeader('Reply-To', "<{$this->fromMail}>");
        $this->setHeader('Return-Path', "<{$this->fromMail}>");
        $this->setHeader('Message-ID', '<' . time() . "-{$this->fromMail}>");
        $this->setHeader('From', $this->address($this->fromMail, $this->fromName));
        $this->setHeader('To', $this->address($this->toMail, $this->toName));
        $this->setHeader('Subject', $this->subject);
        $this->setHeader('Date', date('r'));

        $cmd = $this->headersToString() . self::NL . $this->message . self::NL . '.';

        $this->log('send', $this->sendCmd($cmd));

        $this->log('quit', $this->sendCmd('QUIT'));

        $this->disconnect();

        $sendValue = trim($this->logGetValue('send'));

        $result = (strpos($sendValue, '250') === 0);



        if(!$result) {
            if(strpos($this->log['to'], '250') === 0) {
                trigger_error('SMTP error ' . $sendValue);
            } else {
                $result = true;
            }
        }

        return $result;
    }

    static function configPath()
    {
        return dirname(__FILE__) . '/../config/smtp.php';
    }

    static function isActive()
    {
        static $isActive = null;

        if($isActive === null) {
            if (!Common::isOptionActive('active', 'smtp')) {
                global $g;
                unset($g['smtp']);
                if (file_exists(Smtp::configPath())) {
                    include_once Smtp::configPath();
                    Common::setOptionRuntime('Y', 'active', 'smtp');
                }
            }
            $isActive = Common::isOptionActive('active', 'smtp');
        }

        return $isActive;

        /*
        global $g;
        $result = false;
        if (isset($g['smtp'])) {
            if (trim($g['smtp']['server']) != ''
                && trim($g['smtp']['port']) != ''
                && trim($g['smtp']['user']) != ''
                && trim($g['smtp']['password']) != '') {
                $result = true;
            }
        }
        return $result;
         */
    }


}
