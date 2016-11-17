<?php
/**
 * Created by Enjoy Your Business.
 * Date: 27/11/2015
 * Time: 15:16
 * Copyright 2014 Enjoy Your Business - RCS Bourges B 800 159 295 ©
 */

namespace EnjoyYourBusiness\WebSocketClientBundle\Model;

use Ratchet\ConnectionInterface;

/**
 * Class Message
 *
 * @package   Eyb\HomeBundle\WebSocket
 *
 * @author    Emmanuel Derrien <emmanuel.derrien@enjoyyourbusiness.fr>
 * @author    Anthony Maudry <anthony.maudry@enjoyyourbusiness.fr>
 * @author    Loic Broc <loic.broc@enjoyyourbusiness.fr>
 * @author    Rémy Mantéi <remy.mantei@enjoyyourbusiness.fr>
 * @copyright 2014 Enjoy Your Business - RCS Bourges B 800 159 295 ©
 */
class Message implements \JsonSerializable
{
    const EXCEPTION_NO_ACTION_KEY = 'No key "%s" for action in message.';
    const ACTION_KEY = 'action';
    const CLASS_KEY = 'messageClass';
    const DATA_KEY = 'data';

    const GET_PREFIX = 'get';
    const SET_PREFIX = 'set';
    const IS_PREFIX = 'is';
    const HAS_PREFIX = 'has';
    const MODE_GET = 'MODE_GET';
    const MODE_SET = 'MODE_SET';
    const EXCEPTION_NOT_AN_ACCESSOR_NOR_MUTATOR = 'Method called is nor an accessor nor a mutator, chould start with %s, %s, %s, or %s.';
    const EXCEPTION_NO_PROPERTY = 'No property provided.';
    const EXCEPTION_INVALID_MODE = 'Invalid mode';

    const INVOKE_WRONG_CLASS = 'The message provided a message class "%s", but the class does not exist.';
    const MESSAGE_PARENT_CLASS_NOT_IMPLEMENTED = 'The message provided a message class "%s", but the class does not extend %s.';


    /**
     * @var ConnectionInterface
     */
    private $from;

    /**
     * @var string
     */
    private $action;

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var \DateTime
     */
    private $timestamp;

    /**
     * @var array
     */
    private $to;

    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->timestamp = new \DateTime();
    }

    /**
     * Gets the client connection that "authored" the message
     *
     * @return ConnectionInterface
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Gets the action of the message
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Sets from
     *
     * @param ConnectionInterface $from
     *
     * @return Message
     */
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Sets action
     *
     * @param string $action
     *
     * @return Message
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Sets data
     *
     * @param mixed $data
     *
     * @return Message
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Gets the date
     *
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Sets the recipients for the message
     *
     * @param array $to
     *
     * @return Message
     */
    public function setTo($to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Gets the recpients of the message
     *
     * @return array
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Magic method to call getters and setters on data property
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function __call($name, array $arguments)
    {
        switch (true) {
            case (strstr($name, static::GET_PREFIX)) :
                $mode = static::MODE_GET;
                $prop = substr($name, strlen(static::GET_PREFIX));
                break;
            case (strstr($name, static::IS_PREFIX)) :
                $mode = static::MODE_GET;
                $prop = substr($name, strlen(static::IS_PREFIX));
                break;
            case (strstr($name, static::HAS_PREFIX)) :
                $mode = static::MODE_GET;
                $prop = substr($name, strlen(static::HAS_PREFIX));
                break;
            case (strstr($name, static::SET_PREFIX)) :
                $mode = static::MODE_SET;
                $prop = substr($name, strlen(static::SET_PREFIX));
                break;
            default :
                throw new \Exception(sprintf(
                    static::EXCEPTION_NOT_AN_ACCESSOR_NOR_MUTATOR,
                    static::GET_PREFIX,
                    static::HAS_PREFIX,
                    static::IS_PREFIX,
                    static::SET_PREFIX
                ));
        }

        if (!$prop) {
            throw new \Exception(static::EXCEPTION_NO_PROPERTY);
        }

        $prop = lcfirst($prop);

        switch ($mode) {
            case static::MODE_SET :
                $this->data[$prop] = count($arguments) > 0 ? $arguments[0] : null;

                return $this;
            case static::MODE_GET :

                return array_key_exists($prop, $this->data) ? $this->data[$prop] : null;
            default :
                throw new \Exception(static::EXCEPTION_INVALID_MODE);
        }
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     *
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->data;
    }

    /**
     * Creates a message
     *
     * @param ConnectionInterface $from
     * @param array               $json
     * @param array               $to
     *
     * @return Message
     * @throws \Exception
     */
    public static function create(ConnectionInterface $from, array $json, array $to = [])
    {
        if (array_key_exists(static::CLASS_KEY, $json)) {
            $class = $json[static::CLASS_KEY];
            if (!class_exists($class)) {
                throw new \Exception(sprintf(static::INVOKE_WRONG_CLASS, $class));
            }

            if (!($class instanceof Message)) {
                throw new \Exception(sprintf(static::MESSAGE_PARENT_CLASS_NOT_IMPLEMENTED, $class, Message::class));
            }

            if (!array_key_exists(static::ACTION_KEY, $json)) {
                throw new \Exception(sprintf(static::EXCEPTION_NO_ACTION_KEY, static::ACTION_KEY));
            }
        } else {
            $class = Message::class;
        }

        if (array_key_exists(static::ACTION_KEY, $json)) {
            $action = $json[static::ACTION_KEY];
        } else {
            $action = '';
        }

        if (array_key_exists(static::DATA_KEY, $json)) {
            $data = $json[static::DATA_KEY];
        } else {
            $data = [];
        }

        /** @var Message $instance */
        $instance = new $class();
        $instance->setFrom($from)
            ->setAction($action)
            ->setData($data)
            ->setTo($to);

        return $instance;
    }
}