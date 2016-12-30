<?php
/**
 * Created by Enjoy Your Business.
 * Date: 30/12/2016
 * Time: 10:22
 * Copyright 2014 Enjoy Your Business - RCS Bourges B 800 159 295 ©
 */

namespace EnjoyYourBusiness\WebSocketClientBundle\Model;


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
    /**
     * {@inheritdoc}
     */
    public function open()
    {
        $res = parent::open();

        if ($res) {
            $this->getLogger() and $this->getLogger()->addInfo('Sending probe');
            $probResponse = $this->sendRaw('2probe', true);

            if ($probResponse === '3probe') {
                $this->getLogger() and $this->getLogger()->addInfo('Got response probe');
                $this->sendRaw('5');
                $this->getLogger() and $this->getLogger()->addInfo('Got response probe');
            } else {
                $this->getLogger() and $this->getLogger()->addInfo('Got wrong response probe', [$probResponse]);
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