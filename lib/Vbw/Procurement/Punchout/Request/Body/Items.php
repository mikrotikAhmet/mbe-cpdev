<?php
namespace Vbw\Procurement\Punchout\Request\Body;

use Vbw\Procurement\Punchout;

class Items
    extends Punchout\Data\Access
    implements \Iterator, \Countable
{

    protected $_position = 0;

    protected $_items = array();

    public function __construct($data = null)
    {
        if (is_array($data)) {
            foreach ($data AS $k => $v) {
                $this->_items[] = new Punchout\Data($v);
            }
        }
    }

    public function addItemOut ($primaryId,$quantity,$secondaryId = null)
    {
        $item = $this->addItem($primaryId,$secondaryId);
        $item->set('type','out');
        $item->set('quantity',$quantity);
        return $item;
    }

    public function addSelectedItem ($primaryId,$secondaryId = null)
    {
        $item = $this->addItem($primaryId,$secondaryId);
        $item->set('type','in');
        return $item;
    }

    public function addItem ($primaryId,$secondaryId = null)
    {
        $item = new Punchout\Data;
        $item->set('primaryId',$primaryId);
        $item->set('secondaryId',$secondaryId);
        $this->_items[] = $item;
        return $item;
    }

    public function toArray ()
    {
        $return = array ();
        foreach ($this->_items AS $k => $v) {
            $data = $v->toArray();
            $return[] = $data;
        }
        return $return;
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
        if (isset($this->_items[$this->_position])) {
            return true;
        }
        return false;
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
}
    /*
        <PunchOutSetupRequest
                operation="create">
            <BuyerCookie>BunrmXY8HHaUL18j0.508439296124953916</BuyerCookie>
            <Extrinsic
                name="CostCenter">670</Extrinsic>
			<Extrinsic
                name="UniqueName">catalog_tester</Extrinsic>
			<Extrinsic
                    name="UserEmail">catalog_tester@ariba.com</Extrinsic>
			<BrowserFormPost>
                <URL>https://service.ariba.com/CatalogTester.aw/651389/ad/handlePunchOutOrder/BunrmXY8HHaUL18j0.508439296124953916?awr=2&amp;aws=BunrmXY8HHaUL18j</URL>
            </BrowserFormPost>
            <SupplierSetup>
                <URL>https://www.harperhardwareandtools.com/punchout/request/</URL>
            </SupplierSetup>
            <ShipTo>
                    <Address
                        addressID="26">
	    				<Name
	                        xml:lang="en-US">Catalog Tester</Name>
	    				<PostalAddress
	                            name="_5uicbb">
						    <DeliverTo>Catalog Tester</DeliverTo>
						    <Street>1234 Catalog Tester Way</Street>
						    <City>Sunnyvale</City>
						    <State>CA</State>
						    <PostalCode>94089</PostalCode>
						    <Country isoCountryCode="US">United States</Country>
						</PostalAddress>
			  	  </Address>
            </ShipTo>
            <SelectedItem>
                    <ItemID>
                        <SupplierPartID>AAA</SupplierPartID>
                        <SupplierPartAuxiliaryID/>
                    </ItemID>
                </SelectedItem>
        </PunchOutSetupRequest>
    */