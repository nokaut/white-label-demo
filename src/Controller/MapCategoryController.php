<?php

namespace App\Controller;

use App\Lib\Breadcrumbs\BreadcrumbsFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MapCategoryController extends AbstractController
{
    public function __construct(
        private BreadcrumbsFactory $breadcrumbsFactory
    )
    {
    }

    public function indexAction()
    {
        $breadcrumbs = $this->breadcrumbsFactory->createBreadcrumb("Mapa kategorii");

        return $this->render('MapCategory/index.html.twig', [
            'breadcrumbs' => $breadcrumbs,
        ]);
    }


} 