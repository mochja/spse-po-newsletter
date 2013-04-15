<?php

// goood

namespace Custom;

class Finder extends \Nette\Utils\Finder
{
    private $order;

    /**
     * Sets the order comparison function
     * @param callback $order
     * @return Finder
     */
    public function order($order)
    {
        $this->order = $order;
        return $this;
    }


    public function orderByName()
    {
        $this->order = function($f1, $f2) {
            return \strcasecmp($f1->getFilename(), $f2->getFilename());
        };
        return $this;
    }


    public function orderByMTime()
    {
        $this->order = function($f1, $f2) {
            return $f2->getMTime() - $f2->getMTime();
        };
        return $this;
    }


    /**
     * Returns iterator.
     * @return \Iterator
     */
    public function getIterator()
    {
        $iterator = parent::getIterator();
        if ($this->order === NULL) {
            return $iterator;
        }

        $iterator = new \ArrayIterator(\iterator_to_array($iterator));
        $iterator->uasort($this->order);

        return $iterator;
    }

}