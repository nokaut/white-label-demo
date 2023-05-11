<?php

namespace App\Lib\Breadcrumbs;

use App\Lib\Type\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BreadcrumbsFactory extends AbstractController
{
    public function createBreadcrumb(string $title): array
    {
        return [new Breadcrumb($title)];
    }
}