<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 23.09.2014
 * Time: 14:22
 */

namespace App\Lib\Twig;


use App\Lib\Helper\ClickUrl;
use Nokaut\ApiKit\Entity\EntityAbstract;
use Nokaut\ApiKit\Entity\Offer;
use Nokaut\ApiKit\Entity\Product;
use Nokaut\ApiKit\Entity\Product\OfferWithBestPrice;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ClickUrlExtension extends AbstractExtension
{
    public function __construct(private ClickUrl $clickUrl)
    {
    }


    public function getFilters(): array
    {
        return [
            new TwigFilter('click', [$this, 'clickUrl']),
        ];
    }

    /**
     * @param EntityAbstract $entity
     * @throws \InvalidArgumentException
     * @return string
     */
    function clickUrl($entity)
    {
        if ($entity instanceof Product) {
            return $this->clickUrl->prepareProductClickUrl($entity);
        }
        if ($entity instanceof Offer) {
            return $this->clickUrl->prepareOfferClickUrl($entity);
        }
        if ($entity instanceof OfferWithBestPrice) {
            return $this->clickUrl->prepareOfferClickUrl($entity);
        }
        throw new \InvalidArgumentException("unsupported entity " . get_class($entity) ." for generate click");
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'clickUrl';
    }
} 