<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 23.07.2014
 * Time: 10:17
 */

namespace App\Lib\Type\Rss;


class RssItem
{
    /**
     * @var string
     */
    private $link;
    /**
     * @var string
     */
    private $title;
    /**
     * @var \DateTime
     */
    private $updated;

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param \DateTime $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }


}