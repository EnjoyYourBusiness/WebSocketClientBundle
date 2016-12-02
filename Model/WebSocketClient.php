<?php
/**
 * Created by Enjoy Your Business.
 * Date: 19/01/2016
 * Time: 14:55
 * Copyright 2014 Enjoy Your Business - RCS Bourges B 800 159 295 ©
 */

namespace EnjoyYourBusiness\WebSocketClientBundle\Model;

use EnjoyYourBusiness\WebSocketClientBundle\Exception\Handshake\InvalidAcceptKeyException;
use EnjoyYourBusiness\WebSocketClientBundle\Exception\Handshake\NoHeadersException;
use EnjoyYourBusiness\WebSocketClientBundle\Exception\WebsocketHandShakeException;
use EnjoyYourBusiness\WebSocketClientBundle\Exception\WebsocketMessageException;
use EnjoyYourBusiness\WebSocketClientBundle\Exception\WebsocketOpenException;
use Monolog\Logger;

/**
 * Class WebSocketClient
 *
 * @package   EnjoyYourBusiness\WebSocketClientBundle\Component
 *
 * @author    Emmanuel Derrien <emmanuel.derrien@enjoyyourbusiness.fr>
 * @author    Anthony Maudry <anthony.maudry@enjoyyourbusiness.fr>
 * @author    Loic Broc <loic.broc@enjoyyourbusiness.fr>
 * @author    Rémy Mantéi <remy.mantei@enjoyyourbusiness.fr>
 * @author    Lucien Bruneau <lucien.bruneau@enjoyyourbusiness.fr>
 * @copyright 2014 Enjoy Your Business - RCS Bourges B 800 159 295 ©
 */
final class WebSocketClient
{
    const PUSH_CLIENT_NAME = 'push client';
    const SOCKET_URL_FORMAT = 'tcp://%s:%d';
    const MESSAGE_HANDSHAKE = 'handshake';
    const MESSAGE_WRAP = "\x00%s\xff";
    const ACCEPT_KEY_REGEXP = '#Sec-WebSocket-Accept:\s(.*)$#mU';
    const ACCEPT_GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
    const URL_REGEXP = '#^(?<protocole>\w+://)?(?<url>[^/]+)(?<uri>/.*)?$#';


    /**
     * @var boolean
     */
    private $opened;
    /**
     * @var resource
     */
    private $socket;
    /**
     * @var string
     */
    private $receivedHeaders;
    /**
     * @var string
     */
    private $receivedData;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var string
     */
    private $key;
    /**
     * @var string
     */
    private $host;
    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var bool
     */
    private $ssl;

    /**
     * @var string
     */
    private $clientIp;

    /**
     * @var array
     */
    private $headers;

    /**
     * WebSocketClient constructor.
     *
     * @param string $host
     * @param int    $port
     * @param string $clientIp
     * @param array  $headers
     * @param Logger $logger
     */
    public function __construct(string $host, int $port, string $clientIp, array $headers = [], Logger $logger = null)
    {
        $matches = [];
        preg_match(self::URL_REGEXP, $host, $matches);
        $this->logger = $logger;
        $this->host = $matches['url'];
        $this->port = $port;
        $this->uri = !empty($matches['uri']) ? $matches['uri'] : '/';
        $this->ssl = ($matches['protocole'] === 'wss://');
        $this->clientIp = $clientIp;
        $this->headers = $headers;

        $this->getLogger() and $this->getLogger()->addInfo('Create websocket with configuration : ', [
            'provided host' => $host,
            'matches'       => $matches,
            'host'          => $this->host,
            'port'          => $this->port,
            'uri'           => $this->uri,
            'ssl'           => $this->ssl
        ]);
    }

    /**
     * @return Logger
     */
    private function getLogger()
    {
        return $this->logger;
    }

    /**
     * Gets the current session key
     *
     * @return string
     */
    private function getKey()
    {
        if (!$this->key) {
            $this->key = trim(bin2hex(random_bytes(8)));
        }

        return $this->key;
    }

    /**
     * Gets the expected server accept key
     *
     * @return string
     */
    private function getExpectedServerAcceptKey()
    {
        $concatenated = trim(base64_encode($this->getKey()) . self::ACCEPT_GUID);

        $sha1 = pack('H*', sha1($concatenated));

        return base64_encode($sha1);
    }

    /**
     * Makes and returns a web socket message header
     *
     * @return string
     */
    private function getHandshakeHeaders()
    {
        $protocols = ['chat', 'superchat'];

        $headersList = array_merge([
            'Upgrade'                => 'websocket',
            'Connection'             => 'Upgrade',
            'Origin'                 => $this->clientIp,
            'Host'                   => sprintf('%s:%d', $this->host, $this->port),
            'X-Enjoy-Application'    => 'anthony',
            'Sec-WebSocket-Version'  => '13',
            'Sec-WebSocket-Protocol' => implode(', ', $protocols),
            'Sec-WebSocket-Key'      => base64_encode($this->getKey()),
        ], $this->headers);

        $headers = sprintf("GET %s HTTP/1.1" . "\r\n", $this->uri);

        foreach ($headersList as $key => $value) {
            $headers .= sprintf('%s: %s' . "\r\n", $key, $value);
        }

        $headers .= "\r\n";

        return $headers;
    }

    /**
     * Gets the websocket for that request
     *
     * @return resource
     *
     * @throws \Exception
     */
    private function getSocket()
    {
        if (!is_resource($this->socket)) {
            $this->open();
        }

        return $this->socket;
    }

    /**
     * Validates the handshake response
     *
     * @return bool
     *
     * @throws InvalidAcceptKeyException
     * @throws NoHeadersException
     * @throws \Exception
     */
    private function validateHandshake()
    {
        $headers = $this->getLastReceivedHeaders();

        if (!$headers) {
            $this->getLogger() and $this->getLogger()->addCritical(NoHeadersException::MESSAGE);
            throw new NoHeadersException();
        }

        $matches = [];

        $this->getLogger() and $this->getLogger()->addInfo('Received Headers for handshake : ' . $headers);

        preg_match(self::ACCEPT_KEY_REGEXP, $headers, $matches);

        if (count($matches) > 1) {
            $receivedKey = trim($matches[1]);
        } else {
            throw new \Exception('Did not get an accept key from handshake');
        }

        if ($receivedKey !== $this->getExpectedServerAcceptKey()) {
            $this->getLogger() and $this->getLogger()->addCritical(sprintf(InvalidAcceptKeyException::MESSAGE_FORMAT, $this->getExpectedServerAcceptKey(), $receivedKey));

            throw new InvalidAcceptKeyException($this->getExpectedServerAcceptKey(), $receivedKey);
        }

        return true;
    }

    /**
     * Opens a socket
     *
     * @return bool
     * @throws WebsocketOpenException
     */
    public function open()
    {
        $host = $this->host;
        $port = $this->port;
        $errno = 0;
        $errstr = '';

        if ($this->ssl) {
            $host = 'ssl://' . $host;
        }

        $this->getLogger() and $this->getLogger()->addInfo(sprintf('Opening websocket connection : %s:%d', $host, $port));

        $this->socket = fsockopen($host, $port, $errno, $errstr, 2);

        if (!is_resource($this->socket) or $errno > 0) {
            $this->getLogger() and $this->getLogger()->addCritical(WebsocketOpenException::MESSAGE);
            throw new WebsocketOpenException($errno, $errstr);
        }

        $this->getLogger() and $this->getLogger()->addInfo('Websocket oppened');

        $this->handshake();
        $this->getLogger() and $this->getLogger()->addInfo('Handshake received');

        return true;
    }

    /**
     * "Handshakes" the server, to ensure that connection is opened
     *
     * @throws WebsocketHandShakeException
     *
     * @return bool
     */
    private function handshake()
    {
        $this->getLogger() and $this->getLogger()->addInfo('Sending handshake', [$this->getHandshakeHeaders()]);
        $writeHeadersResult = fwrite($this->getSocket(), $this->getHandshakeHeaders());
        if (!$writeHeadersResult) {
            $this->getLogger() and $this->getLogger()->addCritical(WebsocketHandShakeException::MESSAGE);
            throw new WebsocketHandShakeException();
        }
        $this->receivedHeaders = fread($this->getSocket(), 2000);

        $this->validateHandshake();

        $this->getLogger() and $this->getLogger()->addInfo('handshake done');

        return true;
    }

    /**
     * Closes the connection
     */
    private function close()
    {
        $this->getLogger() and $this->getLogger()->addInfo('closing websocket');
        $this->opened and fclose($this->getSocket());
        $this->opened = false;

        return true;
    }

    /**
     * Sends a message
     *
     * @param mixed $message
     * @param bool  $waitResponse
     *
     * @return string
     * @throws WebsocketMessageException
     */
    public function send($message, $waitResponse = false)
    {
        if (($message instanceof \JsonSerializable) or is_array($message)) {
            $toSend = json_encode($message);
        } elseif (is_scalar($message)) {
            $toSend = (string) $message;
        } else {
            $this->getLogger() and $this->getLogger()->addCritical('A websocket message should be a scalar or a json serializable');
            throw new \InvalidArgumentException('A websocket message should be a scalar or a json serializable');
        }

        $socket = $this->getSocket();

        $this->getLogger() and $this->getLogger()->addInfo('Writing message body', ['message' => $toSend]);
        $writeMessageResult = fwrite($socket, $this->hybi10Encode($toSend));
        if (!$writeMessageResult) {
            $this->getLogger() and $this->getLogger()->addCritical(WebsocketMessageException::MESSAGE_BODY_ERROR);
            throw new WebsocketMessageException(WebsocketMessageException::MESSAGE_BODY_ERROR);
        }
        if ($waitResponse) {
            $response = '';
            $this->getLogger() and $this->getLogger()->addInfo('Reading response');
            while($buffer = fread($this->getSocket(), 2000)) {
                $response .= $buffer;
            }
            $this->getLogger() and $this->getLogger()->addInfo('Read message body response', [$buffer]);

            return $this->hybi10Decode($response);
        }

        return '';
    }

    /**
     * Gets the last received headers
     *
     * @return string
     */
    public function getLastReceivedHeaders()
    {
        return $this->receivedHeaders;
    }

    /**
     * Gets the last received data
     *
     * @return string
     */
    public function getLastReceivedData()
    {
        return $this->receivedData;
    }

    /**
     * destructor
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Encode a message in frames
     *
     * @param mixed  $payload
     * @param string $type
     * @param bool   $masked
     *
     * @return string
     *
     * @see https://tools.ietf.org/html/rfc6455#page-27
     * @link https://github.com/nekudo/php-websocket/blob/master/client/lib/class.websocket_client.php
     *
     * @author Nekudo (https://nekudo.com)
     */
    private function hybi10Encode($payload, $type = 'text', $masked = true)
    {
        $frameHead = array();
        $frame = '';
        $payloadLength = strlen($payload);

        switch ($type) {
            case 'text':
                // first byte indicates FIN, Text-Frame (10000001):
                $frameHead[0] = 129;
                break;

            case 'close':
                // first byte indicates FIN, Close Frame(10001000):
                $frameHead[0] = 136;
                break;

            case 'ping':
                // first byte indicates FIN, Ping frame (10001001):
                $frameHead[0] = 137;
                break;

            case 'pong':
                // first byte indicates FIN, Pong frame (10001010):
                $frameHead[0] = 138;
                break;
        }

        // set mask and payload length (using 1, 3 or 9 bytes)
        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i++) {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }
            // most significant bit MUST be 0 (close connection if frame too big)
            if ($frameHead[2] > 127) {
                $this->close(1004);

                return false;
            }
        } elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }
        // convert frame-head to string:
        foreach (array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        }
        if ($masked === true) {
            // generate a random mask:
            $mask = array();
            for ($i = 0; $i < 4; $i++) {
                $mask[$i] = chr(rand(0, 255));
            }

            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);
        // append payload to frame:
        $framePayload = array();
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }

        return $frame;
    }

    /**
     * Decodes a message from frames
     *
     * @param string $data
     *
     * @return array
     *
     * @see https://tools.ietf.org/html/rfc6455#page-27
     * @link https://github.com/nekudo/php-websocket/blob/master/client/lib/class.websocket_client.php
     *
     * @author Nekudo (https://nekudo.com)
     */
    private function hybi10Decode($data)
    {
        $payloadLength = '';
        $mask = '';
        $unmaskedPayload = '';
        $decodedData = array();

        // estimate frame type:
        $firstByteBinary = sprintf('%08b', ord($data[0]));
        $secondByteBinary = sprintf('%08b', ord($data[1]));
        $opcode = bindec(substr($firstByteBinary, 4, 4));
        $isMasked = ($secondByteBinary[0] == '1') ? true : false;
        $payloadLength = ord($data[1]) & 127;

        switch ($opcode) {
            // text frame:
            case 1:
                $decodedData['type'] = 'text';
                break;

            case 2:
                $decodedData['type'] = 'binary';
                break;

            // connection close frame:
            case 8:
                $decodedData['type'] = 'close';
                break;

            // ping frame:
            case 9:
                $decodedData['type'] = 'ping';
                break;

            // pong frame:
            case 10:
                $decodedData['type'] = 'pong';
                break;

            default:
                return false;
                break;
        }

        if ($payloadLength === 126) {
            $mask = substr($data, 4, 4);
            $payloadOffset = 8;
            $dataLength = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset;
        } elseif ($payloadLength === 127) {
            $mask = substr($data, 10, 4);
            $payloadOffset = 14;
            $tmp = '';
            for ($i = 0; $i < 8; $i++) {
                $tmp .= sprintf('%08b', ord($data[$i + 2]));
            }
            $dataLength = bindec($tmp) + $payloadOffset;
            unset($tmp);
        } else {
            $mask = substr($data, 2, 4);
            $payloadOffset = 6;
            $dataLength = $payloadLength + $payloadOffset;
        }

        if ($isMasked === true) {
            for ($i = $payloadOffset; $i < $dataLength; $i++) {
                $j = $i - $payloadOffset;
                if (isset($data[$i])) {
                    $unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
                }
            }
            $decodedData['payload'] = $unmaskedPayload;
        } else {
            $payloadOffset = $payloadOffset - 4;
            $decodedData['payload'] = substr($data, $payloadOffset);
        }

        return $decodedData;
    }
}