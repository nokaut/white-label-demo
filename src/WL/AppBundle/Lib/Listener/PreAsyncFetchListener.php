<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 24.07.2014
 * Time: 15:03
 */

namespace WL\AppBundle\Lib\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

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

    public function onKernelController(FilterControllerEvent $event)
    {
        if (is_null(self::$done)) {
            //set request to API for all categories
            $this->container->get('categories.all');

            self::$done = true;
        }
    }
}