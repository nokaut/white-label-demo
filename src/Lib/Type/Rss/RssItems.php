<?php

namespace App\Lib\Type\Rss;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

class RssItems implements Countable, IteratorAggregate
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

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}