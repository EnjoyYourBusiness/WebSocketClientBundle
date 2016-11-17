<?php
/**
 * Created by Enjoy Your Business.
 * Date: 28/01/2016
 * Time: 11:09
 * Copyright 2014 Enjoy Your Business - RCS Bourges B 800 159 295 ©
 */

namespace EnjoyYourBusiness\WebSocketClientBundle\Exception;

/**
 * Class WebsocketHandShakeException
 *
 * @package   EnjoyYourBusiness\WebSocketClientBundle\Exception
 *
 * @author    Emmanuel Derrien <emmanuel.derrien@enjoyyourbusiness.fr>
 * @author    Anthony Maudry <anthony.maudry@enjoyyourbusiness.fr>
 * @author    Loic Broc <loic.broc@enjoyyourbusiness.fr>
 * @author    Rémy Mantéi <remy.mantei@enjoyyourbusiness.fr>
 * @author    Lucien Bruneau <lucien.bruneau@enjoyyourbusiness.fr>
 * @author    Matthieu Prieur <matthieu.prieur@enjoyyourbusiness.fr>
 * @copyright 2014 Enjoy Your Business - RCS Bourges B 800 159 295 ©
 */
class WebsocketHandShakeException extends \Exception
{
    const MESSAGE = 'An exception occured while handshaking';
    const ERROR_CODE = 500;

    /**
     * Construct the exception. Note: The message is NOT binary safe.
     *
     * @link http://php.net/manual/en/exception.construct.php
     *
     * @param Exception $previous [optional] The previous exception used for the exception chaining. Since 5.3.0
     *
     * @since 5.1.0
     */
    public function __construct(\Exception $previous = null)
    {
        parent::__construct(self::MESSAGE, self::ERROR_CODE, $previous);
    }

}