<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 09.01.15
 * Time: 09:45
 */

namespace App\Lib\Guzzle\Subscriber;


use Guzzle\Common\Event;
use Guzzle\Http\Message\Request;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UrlEncodeSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents(): array
    {
        return [
            'request.before_send' => 'onRequestBeforeSend',
        ];
    }

    public function onRequestBeforeSend(Event $event)
    {
        /** @var Request $request */
        $request = $event['request'];
        $request->getQuery()->useUrlEncoding(false);
    }
} 