<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 23.07.2014
 * Time: 10:11
 */

namespace WL\AppBundle\Lib\Type\Rss;


use ArrayIterator;
use Debril\RssAtomBundle\Protocol\Parser\Item;

class RssItems implements \Countable, \IteratorAggregate
{
    /**
     * @var Item[]
     */
    private $items;

    function __construct(array $items)
    {
        $this->convertToSerializeObject($items);
    }

    /**
     * @param Item[] $items
     */
    public function convertToSerializeObject(array $items)
    {
        foreach ($items as $item) {
            $rssItem = new RssItem();
            $rssItem->setLink((string)$item->getLink());
            $rssItem->setTitle((string)$item->getTitle());
            $rssItem->setUpdated($item->getUpdated());
            $this->items[] = $rssItem;
        }
    }

    public function count()
    {
        return count($this->items);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }
}