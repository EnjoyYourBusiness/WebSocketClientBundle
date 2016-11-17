<?php
/**
 * Created by Enjoy Your Business.
 * Date: 28/01/2016
 * Time: 11:03
 * Copyright 2014 Enjoy Your Business - RCS Bourges B 800 159 295 ©
 */

namespace EnjoyYourBusiness\WebSocketClientBundle\Exception;

/**
 * Class WebsocketOpenException
 *
 * @package   EnjoyYourBusiness\WebSocketClientBundle\Exception
 *
 * @author    Emmanuel Derrien <emmanuel.derrien@enjoyyourbusiness.fr>
 * @author    Anthony Maudry <anthony.maudry@enjoyyourbusiness.fr>
 * @author    Loic Broc <loic.broc@enjoyyourbusiness.fr>
 * @author    Rémy Mantéi <remy.mantei@enjoyyourbusiness.fr>
 * @author    Lucien Bruneau <lucien.bruneau@enjoyyourbusiness.fr>
 * @copyright 2014 Enjoy Your Business - RCS Bourges B 800 159 295 ©
 */
class WebsocketOpenException extends \Exception
{
    const MESSAGE = 'An error occured while opening a websocket client connection : (%d) - %s';
    const ERROR_CODE = 500;

    /**
     * WebsocketOpenException constructor.
     *
     * @param string $errno
     * @param int    $errmess
     */
    public function __construct($errno, $errmess)
    {
        parent::__construct(sprintf(self::MESSAGE, $errno, $errmess), self::ERROR_CODE);
    }
}