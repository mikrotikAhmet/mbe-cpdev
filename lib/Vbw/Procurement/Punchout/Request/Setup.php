<?php
namespace Vbw\Procurement\Punchout\Request;

require_once "Vbw/Procurement/Punchout/Data/Access.php";
require_once "Vbw/Procurement/Punchout/Request/Body/Shipping.php";
require_once "Vbw/Procurement/Punchout/Request/Body/Items.php";
require_once "Vbw/Procurement/Punchout/Request/Body/Contact.php";

use Vbw\Procurement\Punchout;

class Setup
    extends Punchout\Data\Access
{

    protected $_buyerCookie = null;

    protected $_postForm = null;

    protected $_supplierUrl = null;

    protected $_shipping = null;

    protected $_items = null;

    protected $_contact = null;

    public function inflate ($data)
    {
        if (is_array($data)) {
            foreach ($data AS $key => $value) {
                switch (strtolower($key))  {
                    case "buyercookie":
                    case "postform":
                    case "supplierurl":
                    case "items":
                    case "data":
                    case "contact":
                    case "shipping":
                        $this->{"set". $key}($value);
                        break;
                }
            }
        }
    }

    /**
     * @return \Vbw\Procurement\Punchout\Request\Body\Contact
     */
    public function getContact ()
    {
        if ($this->_contact === null) {
            $this->_contact = new Punchout\Request\Body\Contact();
        }
        return $this->_contact;
    }

    public function setContact ($data)
    {
        if (is_array($data)) {
            $this->getContact()->inflate($data);
        }
        return $this->getContact();
    }

    /**
     * @return \Vbw\Procurement\Punchout\Request\Body\Shipping
     */
    public function getShipping ()
    {
        if ($this->_shipping === null) {
            $this->_shipping = new Punchout\Request\Body\Shipping();
        }
        return $this->_shipping;
    }

    public function setShipping ($data)
    {
        if (is_array($data)) {
            $this->getShipping()->setData($data['data']);
        }
        return $this->getShipping();
    }

    /**
     * @return \Vbw\Procurement\Punchout\Request\Body\Items
     */
    public function getItems ()
    {
        if ($this->_items === null) {
            $this->_items = new Punchout\Request\Body\Items();
        }
        return $this->_items;
    }


    public function setItems ($data)
    {
        if (is_array($data)) {
            $this->_items = new Punchout\Request\Body\Items($data);
        }
        return $this->_items;
    }

    public function setBuyerCookie($buyerCookie)
    {
        $this->_buyerCookie = $buyerCookie;
    }

    public function getBuyerCookie()
    {
        return $this->_buyerCookie;
    }

    public function setPostForm($postform)
    {
        $this->_postForm = $postform;
    }

    public function getPostForm()
    {
        return $this->_postForm;
    }

    public function setSupplierUrl($supplierUrl)
    {
        $this->_supplierUrl = $supplierUrl;
    }

    public function getSupplierUrl()
    {
        return $this->_supplierUrl;
    }

    public function toArray ()
    {
        $data = parent::toArray();
        $data['contact'] = $this->getContact()->toArray();
        $data['buyercookie'] = $this->getBuyerCookie();
        $data['postform'] = $this->getPostForm();
        $data['shipping'] = $this->getShipping()->toArray();
        $data['items'] = $this->getItems()->toArray();
        return $data;
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