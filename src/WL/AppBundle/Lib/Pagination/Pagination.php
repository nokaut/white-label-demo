<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 31.03.2014
 * Time: 21:39
 */

namespace WL\AppBundle\Lib\Pagination;


class Pagination
{
    /**
     * @var int
     */
    private $currentPage;
    /**
     * @var int
     */
    private $limit = 24;
    /**
     * @var int
     */
    private $total;
    /**
     * @var int
     */
    private $delta = 12;

    /**
     * @var string
     */
    private $urlTemplate;

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $pageNumber
     */
    public function setCurrentPage($pageNumber)
    {
        $pageNumber = intval($pageNumber);
        if ($pageNumber < 1) {
            $pageNumber = 1;
        }
        if ($pageNumber > $this->total) {
            $pageNumber = $this->total;
        }
        $this->currentPage = $pageNumber;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @param int $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    public function getLastPageNum()
    {
        return $this->getTotal();
    }

    /**
     * @return int
     */
    public function getDelta()
    {
        return $this->delta;
    }

    /**
     * @param int $delta
     */
    public function setDelta($delta)
    {
        $this->delta = $delta;
    }

    /**
     * @param string $urlTemplate
     */
    public function setUrlTemplate($urlTemplate)
    {
        $this->urlTemplate = urldecode($urlTemplate);
    }

    public function isFirstPage()
    {
        if ($this->currentPage == 1) {
            return true;
        }
        return false;
    }

    public function isLastPage()
    {
        return $this->total == $this->currentPage;
    }

    /**
     * @return array
     */
    public function getDeltaPagesNum()
    {
        $middleNumber = $this->calculateMiddleNumber();
        $lastPageNum = $this->total;

        if (($this->currentPage - $middleNumber < 0) || ($this->delta >= $lastPageNum)) {
            return $this->getPutOrderPageNumbers(1);
        }
        if ($this->currentPage + $middleNumber > $lastPageNum) {
            return $this->getPutOrderPageNumbers($lastPageNum - $this->delta + 1);
        }

        return $this->getPutOrderPageNumbers($this->currentPage - $middleNumber + 1);
    }

    /**
     * @param int $numberFrom
     * @return array
     */
    private function getPutOrderPageNumbers($numberFrom)
    {
        $result = array();

        $increasesLimit = min($this->delta, $this->total);
        for ($i = 0; $i < $increasesLimit; ++$i) {
            $result[] = $numberFrom + $i;
        }

        return $result;
    }

    /**
     * @param int $pageNumber
     * @return string
     */
    public function getPageUrl($pageNumber)
    {
        return str_replace("%d", $pageNumber, $this->urlTemplate);
    }

    private function calculateMiddleNumber()
    {
        $half = $this->delta / 2;
        if ($this->delta % 2) {
            return floor($half) + 1;
        }
        return $half;
    }

    public function showLastPage()
    {
        $lastPageNum = $this->total;
        return $this->currentPage <= ($lastPageNum - $this->calculateMiddleNumber()) && $this->delta < $lastPageNum;
    }

    public function showFirstPage()
    {
        $lastPageNum = $this->total;
        return $this->currentPage > $this->calculateMiddleNumber() && $this->delta < $lastPageNum;
    }
} 