<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 26.07.2014
 * Time: 11:13
 */

namespace WL\AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use WL\AppBundle\Lib\Type\Breadcrumb;

class MapCategoryController extends Controller
{
    public function indexAction()
    {

        $breadcrumbs = array();
        $breadcrumbs[] = new Breadcrumb("Mapa kategorii");

        return $this->render('WLAppBundle:MapCategory:index.html.twig', array(
            'breadcrumbs' => $breadcrumbs,
        ));
    }
} 