<?php
/**
 * Created by Enjoy Your Business.
 * Date: 30/12/2016
 * Time: 10:22
 * Copyright 2014 Enjoy Your Business - RCS Bourges B 800 159 295 ©
 */

namespace EnjoyYourBusiness\WebSocketClientBundle\Model;

use EnjoyYourBusiness\WebSocketClientBundle\Model\SocketIO\ProbeResponse;
use Monolog\Logger;


/**
 * Class SocketIOClient
 *
 * @package   EnjoyYourBusiness\websocketclientbundle\Model
 *
 * @author    Emmanuel Derrien <emmanuel.derrien@enjoyyourbusiness.fr>
 * @author    Anthony Maudry <anthony.maudry@enjoyyourbusiness.fr>
 * @author    Loic Broc <loic.broc@enjoyyourbusiness.fr>
 * @author    Rémy Mantéi <remy.mantei@enjoyyourbusiness.fr>
 * @author    Lucien Bruneau <lucien.bruneau@enjoyyourbusiness.fr>
 * @author    Matthieu Prieur <matthieu.prieur@enjoyyourbusiness.fr>
 * @copyright 2014 Enjoy Your Business - RCS Bourges B 800 159 295 ©
 */
class SocketIOClient extends WebSocketClient
{
    const SEND_PROBE_MESSAGE = '2probe';
    const RECEIVED_PROBE_MESSAGE = '3probe';
    const SEND_ACKNOWLEDGEMENT = '5';
    const EIO = '3';
    const TRANSPORT = 'websocket';

    /**
     * @var ProbeResponse
     */
    private $payload;

    public function __construct($host, $port, $clientIp, array $headers = [], Logger $logger = null)
    {
        $params = ['EIO' => self::EIO, 'transport' => self::TRANSPORT];

        $newHost = $host;

        if (strpos($host, '?') === false) {
            $newHost .= '?';
        } else {
            $newHost.= '&';
        }

        $paramsStr = [];

        foreach ($params as $key => $value) {
            $paramsStr[] = sprintf('%s=%s', $key, urlencode($value));
        }

        $newHost .= implode('&', $paramsStr);

        parent::__construct($newHost, $port, $clientIp, $headers, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function open()
    {
        $res = parent::open();

        if ($res) {
            $this->getLogger() and $this->getLogger()->addInfo('Sending probe');
            $probResponse = $this->sendRaw(self::SEND_PROBE_MESSAGE, true);

            if (is_array($probResponse)) {
                $this->payload = new ProbeResponse($probResponse);
                $this->getLogger() and $this->getLogger()->addInfo('Got response probe');
                $this->sendRaw(self::SEND_ACKNOWLEDGEMENT);
                $this->getLogger() and $this->getLogger()->addInfo('Sent acknowledgment');
            } else {
                $this->getLogger() and $this->getLogger()->addInfo('Got wrong response probe');
                return false;
            }
        }

        return $res;
    }

    /**
     * {@inheritdoc}
     */
    public function sendRaw($message, $waitResponse = false)
    {
        return parent::send($message, $waitResponse);
    }

    /**
     * {@inheritdoc}
     */
    public function send($message, $waitResponse = false)
    {
        return parent::send('42' . $message, $waitResponse);
    }
}