<?php
/**
 * Created by Enjoy Your Business.
 * Date: 28/01/2016
 * Time: 11:11
 * Copyright 2014 Enjoy Your Business - RCS Bourges B 800 159 295 ©
 */

namespace EnjoyYourBusiness\WebSocketBundle\Exception;

/**
 * Class WebsocketMessageException
 *
 * @package   EnjoyYourBusiness\WebSocketBundle\Exception
 *
 * @author    Emmanuel Derrien <emmanuel.derrien@enjoyyourbusiness.fr>
 * @author    Anthony Maudry <anthony.maudry@enjoyyourbusiness.fr>
 * @author    Loic Broc <loic.broc@enjoyyourbusiness.fr>
 * @author    Rémy Mantéi <remy.mantei@enjoyyourbusiness.fr>
 * @author    Lucien Bruneau <lucien.bruneau@enjoyyourbusiness.fr>
 * @author    Matthieu Prieur <matthieu.prieur@enjoyyourbusiness.fr>
 * @copyright 2014 Enjoy Your Business - RCS Bourges B 800 159 295 ©
 */
class WebsocketMessageException extends \Exception
{
    const MESSAGE_HEADERS_ERROR = 'An error occured while sending headers';
    const MESSAGE_BODY_ERROR = 'An error occured while sending message body';
}