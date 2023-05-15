<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 24.07.2014
 * Time: 15:03
 */

namespace App\Lib\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class PreAsyncFetchListener
{
    /**
     * @var ContainerInterface
     */
    private $container;
    private static $done;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelController(ControllerEvent $event)
    {
        if (is_null(self::$done)) {
            //todo

            self::$done = true;
        }
    }
}