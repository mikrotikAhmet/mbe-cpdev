<?php
namespace Vbw\Procurement\Punchout\Catalog;

require_once "Vbw/Procurement/Punchout/Data/Access.php";
require_once "Vbw/Procurement/Punchout/Catalog/Item.php";

use Vbw\Procurement\Punchout;

class Items
    extends Punchout\Data\Access
    implements \Countable, \SeekableIterator
{

    protected $_position = 0;

    protected $_items = array();

    public function __construct($data = null)
    {
        if (is_array($data)) {
            $this->inflate($data);
        }
    }

    public function inflate ($data)
    {
        if (is_array($data)) {
            foreach ($data AS $key => $value) {
                $this->{"set". $key}($value);
            }
        }
    }

    public function setItems ($data)
    {
        foreach ($data AS $k => $itemData) {
            $item = $this->newItem();
            $item->inflate($itemData);
        }
    }

    public function &newItem ()
    {
        $nextKey = $this->count();
        $this->_items[] = new Item();
        return $this->_items[$nextKey];
    }

    public function &addItem ($data)
    {
        if (is_array($data)) {
            $item = new Item($data);
        } elseif ($data instanceof Item) {
            $item = $data;
        }
        $nextKey = $this->count();
        $this->_items[] = $item;
        return $this->_items[$nextKey];
    }

    public function toArray ()
    {
        $data = parent::toArray();
        foreach ($this->_items AS $item) {
            $itemData = $item->toArray();
            $data['items'][] = $itemData;
        }
        return $data;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function &current()
    {
        return $this->_items[$this->_position];
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->_position += 1;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return scalar scalar on success, integer
     * 0 on failure.
     */
    public function key()
    {
        return $this->_position;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return isset($this->_items[$this->_position]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->_position = 0;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->_items);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Seeks to a position
     * @link http://php.net/manual/en/seekableiterator.seek.php
     * @param int $position <p>
     * The position to seek to.
     * </p>
     * @return void
     */
    public function seek($position)
    {
        $this->_position = $position;
    }
}
