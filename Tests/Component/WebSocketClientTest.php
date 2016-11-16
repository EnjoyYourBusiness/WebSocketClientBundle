<?php
/**
 * Created by Enjoy Your Business.
 * Date: 29/01/2016
 * Time: 08:35
 * Copyright 2014 Enjoy Your Business - RCS Bourges B 800 159 295 ©
 */

namespace EnjoyYourBusiness\WebSocketBundle\Tests\Component;


use Eyb\BaseBundle\Tests\AbstractTestCase;
use EnjoyYourBusiness\WebSocketBundle\Component\WebSocketClient;

/**
 * Class WebSocketClientTest
 *
 * @package   EnjoyYourBusiness\WebSocketBundle\Tests\Component
 *
 * @author    Emmanuel Derrien <emmanuel.derrien@enjoyyourbusiness.fr>
 * @author    Anthony Maudry <anthony.maudry@enjoyyourbusiness.fr>
 * @author    Loic Broc <loic.broc@enjoyyourbusiness.fr>
 * @author    Rémy Mantéi <remy.mantei@enjoyyourbusiness.fr>
 * @author    Lucien Bruneau <lucien.bruneau@enjoyyourbusiness.fr>
 * @copyright 2014 Enjoy Your Business - RCS Bourges B 800 159 295 ©
 */
class WebSocketClientTest extends AbstractTestCase
{
    /**
     * test_getInstance
     */
    public function test_getInstance()
    {
        $ws = WebSocketClient::getInstance();

        $this->assertInstanceOf(WebSocketClient::class, $ws);
    }

    /**
     * test_open
     */
    public function test_open()
    {
        $ws = WebSocketClient::getInstance();

        $res = $ws->open();

        $this->assertTrue($res);
    }

    /**
     * test_message
     */
    public function test_message()
    {
        $ws = WebSocketClient::getInstance();

        $ws->open();

        $res = $ws->send('Hello world !');

        $this->assertEquals('', $res);
    }

    /**
     * test_message
     */
    public function test_event()
    {
        $ws = WebSocketClient::getInstance();

        $ws->open();

        $res = $ws->sendEvent('test', [ 'message' => 'unit test' ]);

        $this->assertEquals('', $res);
    }
}