<?php

namespace WL\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ClickController extends Controller
{

    public function clickRedirectAction($clickUrl)
    {
        return $this->redirect($this->container->getParameter('click_domain') . urldecode($clickUrl));
    }
}
