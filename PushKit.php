<?php
/**
 * Created by PhpStorm.
 * User: jinya
 * Date: 2018/1/31
 * Time: 下午2:41
 */

class PushKit
{
    private $passphrase = '(VoIPKey.pem pass phrase)';

    private $host = "ssl://gateway.push.apple.com:2195";
    private $timeout = 60;

    public function __construct(){
        if(!defined(ENV_DEV) || ENV_DEV) {
            $this->host = "ssl://gateway.sandbox.push.apple.com:2195";
        }
    }

    public  function VoIP($deviceToken, $message)
    {

        $ctx = stream_context_create([
            'ssl' => [
                'local_cert' => __DIR__ . '/VoIP/ck.pem',
                'passphrase' => $this->passphrase,
                'verify_peer' => false,
            ]
        ]);

        $fp = stream_socket_client($this->host, $err, $errstr, $this->timeout, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

        if (!$fp) {
            exit("Failed to connect: $err $errstr" . PHP_EOL);
        }

        echo 'Connected to APNS' . PHP_EOL;
        // Create the payload body
        $body['message'] = $message;
        // Encode the payload as JSON
        $payload = json_encode($body);
        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));
        if (!$result) {
            echo 'Message not delivered' . PHP_EOL;
        } else {
            echo 'Message successfully delivered' . PHP_EOL;
        }

        // Close the connection to the server
        fclose($fp);

    }
}
