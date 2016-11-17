<?php
/**
 * Created by Enjoy Your Business.
 * Date: 29/01/2016
 * Time: 09:52
 * Copyright 2014 Enjoy Your Business - RCS Bourges B 800 159 295 ©
 */

namespace EnjoyYourBusiness\WebSocketClientBundle\Exception\Handshake;

/**
 * Class NoHeadersException
 *
 * @package   EnjoyYourBusiness\WebSocketClientBundle\Exception\Handshake
 *
 * @author    Emmanuel Derrien <emmanuel.derrien@enjoyyourbusiness.fr>
 * @author    Anthony Maudry <anthony.maudry@enjoyyourbusiness.fr>
 * @author    Loic Broc <loic.broc@enjoyyourbusiness.fr>
 * @author    Rémy Mantéi <remy.mantei@enjoyyourbusiness.fr>
 * @author    Lucien Bruneau <lucien.bruneau@enjoyyourbusiness.fr>
 * @copyright 2014 Enjoy Your Business - RCS Bourges B 800 159 295 ©
 */
class NoHeadersException extends \Exception
{
    const MESSAGE = 'No header or empty headers received in response of handshake';
    const ERROR_CODE = 500;

    /**
     * NoHeadersException constructor.
     */
    public function __construct()
    {
        parent::__construct(self::MESSAGE, self::ERROR_CODE, null);
    }
}