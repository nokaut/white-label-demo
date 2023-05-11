<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 09.09.2014
 * Time: 16:45
 */

namespace App\Lib\Twig;

use Nokaut\ApiKit\Collection\CollectionAbstract;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ChunkExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return array(
            new TwigFilter('chunk', [$this, 'chunk']),
        );
    }

    /**
     * @param array $input
     * @param int $size
     *
     * @return array
     */
    function chunk($input, $size)
    {
        if ($input instanceof CollectionAbstract) {
            $input = $input->getEntities();
        }
        return array_chunk($input, $size);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'chunk';
    }
} 