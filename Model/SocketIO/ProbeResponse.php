<?php
/**
 * Created by Enjoy Your Business.
 * Date: 04/01/2017
 * Time: 17:03
 * Copyright 2014 Enjoy Your Business - RCS Bourges B 800 159 295 ©
 */

namespace EnjoyYourBusiness\WebSocketClientBundle\Model\SocketIO;

/**
 * Class ProbeResponse
 *
 * @package   EnjoyYourBusiness\websocketclientbundle\Model\SocketIO
 *
 * @author    Emmanuel Derrien <emmanuel.derrien@enjoyyourbusiness.fr>
 * @author    Anthony Maudry <anthony.maudry@enjoyyourbusiness.fr>
 * @author    Loic Broc <loic.broc@enjoyyourbusiness.fr>
 * @author    Rémy Mantéi <remy.mantei@enjoyyourbusiness.fr>
 * @author    Lucien Bruneau <lucien.bruneau@enjoyyourbusiness.fr>
 * @author    Matthieu Prieur <matthieu.prieur@enjoyyourbusiness.fr>
 * @copyright 2014 Enjoy Your Business - RCS Bourges B 800 159 295 ©
 */
class ProbeResponse
{
    /**
     * @var string
     */
    private $sid;
    /**
     * @var array
     */
    private $upgrades;
    /**
     * @var int
     */
    private $pingInterval;
    /**
     * @var int
     */
    private $pingTimeout;

    /**
     * ProbeResponse constructor.
     *
     * @param array $payload
     *
     * @throws \Exception
     */
    public function __construct(array $payload)
    {
        if (!array_key_exists('type', $payload) || $payload['type'] !== 'text') {
            throw new \Exception('Invalid payload type, "text" expected');
        }
        if (!array_key_exists('payload', $payload)) {
            throw new \Exception('No payload key in payload');
        }
        if (substr($payload['payload'], 0, 1) !== '0') {
            throw new \Exception('Payload not prefixed by 0');
        }

        $payloadData = json_decode(substr($payload['payload'], 1), true);

        var_dump($payloadData);

        if (!array_key_exists('sid', $payloadData)) {
            throw new \Exception('No sid in payload');
        }

        $this->sid = $payloadData['sid'];

        if (!array_key_exists('upgrades', $payloadData)) {
            throw new \Exception('No upgrades in payload');
        }

        $this->upgrades = $payloadData['upgrades'];

        if (!array_key_exists('pingInterval', $payloadData)) {
            throw new \Exception('No pingInterval in payload');
        }

        $this->pingInterval = $payloadData['pingInterval'];

        if (!array_key_exists('pingTimeout', $payloadData)) {
            throw new \Exception('No pingTimeout in payload');
        }

        $this->pingTimeout = $payloadData['pingTimeout'];
    }

    /**
     * Gets sid
     *
     * @return string
     */
    public function getSid(): string
    {
        return $this->sid;
    }

    /**
     * Gets upgrades
     *
     * @return array
     */
    public function getUpgrades(): array
    {
        return $this->upgrades;
    }

    /**
     * Gets pingInterval
     *
     * @return int
     */
    public function getPingInterval(): int
    {
        return $this->pingInterval;
    }

    /**
     * Gets pingTimeout
     *
     * @return int
     */
    public function getPingTimeout(): int
    {
        return $this->pingTimeout;
    }
}