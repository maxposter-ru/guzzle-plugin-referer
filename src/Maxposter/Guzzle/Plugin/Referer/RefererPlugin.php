<?php
namespace Maxposter\Guzzle\Plugin\Referer;

use Guzzle\Common\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class RefererPlugin
 *
 * @package Maxposter\Guzzle\Plugin\Referer
 */
class RefererPlugin implements EventSubscriberInterface
{
    private $lastAbsoluteUri = null;


    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            'request.before_send' => 'onRequestBeforeSend',
            'request.success'     => 'onRequestSuccess',
        );
    }


    /**
     * Подставляет заголовок Referer при его отсутствии
     *
     * @param \Guzzle\Common\Event $event
     * @return void
     */
    public function onRequestBeforeSend(Event $event)
    {
        /** @var \Guzzle\Http\Message\RequestInterface $request */
        $request = $event['request'];

        if (!$request->hasHeader('Referer') && (false != $this->lastAbsoluteUri)) {
            $request->setHeader('Referer', $this->lastAbsoluteUri);
        }
    }


    /**
     * Запоминает последний ответ сайта
     *
     * @param \Guzzle\Common\Event $event
     * @return void
     */
    public function onRequestSuccess(Event $event)
    {
        /** @var \Guzzle\Http\Message\Response $response */
        $response = $event['response'];
        if (!$response->isRedirect()) {
            $this->lastAbsoluteUri = $response->getEffectiveUrl();
        }
    }


    /**
     * Аналог "набрать адрес ручками"
     *
     * @return void
     */
    public function reset()
    {
        $this->lastAbsoluteUri = null;
    }
}