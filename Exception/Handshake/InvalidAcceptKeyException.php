<?php
/**
 * Created by Enjoy Your Business.
 * Date: 29/01/2016
 * Time: 09:54
 * Copyright 2014 Enjoy Your Business - RCS Bourges B 800 159 295 ©
 */

namespace EnjoyYourBusiness\WebSocketBundle\Exception\Handshake;


/**
 * Class InvalidAcceptKeyException
 *
 * @package   EnjoyYourBusiness\WebSocketBundle\Exception\Handshake
 *
 * @author    Emmanuel Derrien <emmanuel.derrien@enjoyyourbusiness.fr>
 * @author    Anthony Maudry <anthony.maudry@enjoyyourbusiness.fr>
 * @author    Loic Broc <loic.broc@enjoyyourbusiness.fr>
 * @author    Rémy Mantéi <remy.mantei@enjoyyourbusiness.fr>
 * @author    Lucien Bruneau <lucien.bruneau@enjoyyourbusiness.fr>
 * @copyright 2014 Enjoy Your Business - RCS Bourges B 800 159 295 ©
 */
class InvalidAcceptKeyException extends \Exception
{
    const MESSAGE_FORMAT = 'Accept key received is invalid, expected "%s", got "%s".';
    const ERROR_CODE = 500;

    /**
     * InvalidAcceptKeyException constructor.
     *
     * @param string $expected
     * @param string $received
     */
    public function __construct($expected, $received)
    {
        parent::__construct(sprintf(self::MESSAGE_FORMAT, $expected, $received), self::ERROR_CODE, null);
    }
}