<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ClickController extends AbstractController
{
    public function clickRedirectAction($clickUrl)
    {
        return $this->redirect($this->getParameter('click_domain') . urldecode($clickUrl));
    }
}
