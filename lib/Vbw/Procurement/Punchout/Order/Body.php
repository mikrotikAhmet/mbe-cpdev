<?php
namespace Vbw\Procurement\Punchout\Order;

require_once "Vbw/Procurement/Punchout/Data/Access.php";
require_once "Vbw/Procurement/Punchout/Order/Items.php";

use Vbw\Procurement\Punchout;

class Body
    extends Punchout\Data\Access
{

    protected $_buyerCookie = null;

    protected $_total = 0;

    protected $_currency = "USD";

    protected $_items = null;

    public function inflate ($data)
    {
        if (is_array($data)) {
            foreach ($data AS $key => $value) {
                switch (strtolower($key))  {
                    case "buyercookie":
                    case "items":
                    case "total":
                    case "currency":
                    case "data":
                        $this->{"set". $key}($value);
                        break;
                }
            }
        }
    }

    /**
     * @return \Vbw\Procurement\Punchout\Order\Items
     */
    public function getItems ()
    {
        if ($this->_items === null) {
            $this->_items = new Punchout\Order\Items();
        }
        return $this->_items;
    }


    public function setItems ($data)
    {
        if (is_array($data)) {
            $this->_items = new Punchout\Order\Items($data);
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

    public function toArray ()
    {
        $data = parent::toArray();
        $data['buyercookie'] = $this->getBuyerCookie();
        $data['items'] = $this->getItems()->toArray();
        $data['total'] = $this->getTotal();
        $data['currency'] = $this->getCurrency();
        return $data;
    }

    public function setCurrency($currency)
    {
        $this->_currency = $currency;
    }

    public function getCurrency()
    {
        return $this->_currency;
    }

    public function setTotal($total)
    {
        $this->_total = $total;
    }

    public function getTotal()
    {
        return $this->_total;
    }

}
    /*

<!DOCTYPE cXML SYSTEM &#34;http://xml.cxml.org/schemas/cXML/1.2.014/cXML.dtd&#34;>
<cXML payloadID=&#34;958074737352&amp;www.workchairs.com&#34;
timestamp=&#34;2004-06-14T12:59:09-07:00&#34;>
<Header>
	<From>
		<Credential domain=&#34;DUNS&#34;>
			<Identity>12345678</Identity>
		</Credential>
	</From>
	<To>
		<Credential domain=&#34;NetworkId&#34;>
			<Identity>AN01000002792</Identity>
		</Credential>
	</To>
	<Sender>
		<Credential domain=&#34;www.workchairs.com&#34;>
			<Identity>PunchoutResponse</Identity>
		</Credential>
		<UserAgent>Our PunchOut Site V4.2</UserAgent>
	</Sender>
</Header>
<Message>
    <PunchOutOrderMessage>
        <BuyerCookie>1J3YVWU9QWMTB</BuyerCookie>
        <PunchOutOrderMessageHeader operationAllowed=&#34;edit&#34;>
            <Total>
                <Money currency=&#34;USD&#34;>14.27</Money>
            </Total>
        </PunchOutOrderMessageHeader>
        <ItemIn quantity=&#34;2&#34;>
            <ItemID>
                <SupplierPartID>3171 04 20</SupplierPartID>
                <UnitOfMeasure>EA</UnitOfMeasure>
                <Classification domain=&#34;UNSPSC&#34;>21101510</Classification>
                <ManufacturerName>Dogwood</ManufacturerName>
            </ItemDetail>
        </ItemIn>
        <ItemIn quantity=&#34;1&#34;>
            <ItemID>
                <SupplierPartID>3801 04 20</SupplierPartID>
                <SupplierPartAuxiliaryID>
                    ContractId=1751 ItemId=417769
                </SupplierPartAuxiliaryID>
            </ItemID>
            <ItemDetail>
                <UnitPrice>
                    <Money currency=&#34;USD&#34;>11.83</Money>
                </UnitPrice>
                <Description xml:lang=&#34;en&#34;>ADAPTER; TUBE; 5/32&#34;; 2 PER PACK; MALE
                    #10-32 UNF; STAINLESS STEEL; FITTING</Description>
                <UnitOfMeasure>EA</UnitOfMeasure>
                <Classification domain=&#34;UNSPSC&#34;>21101510</Classification>
                <ManufacturerName>Legris</ManufacturerName>
                <LeadTime>2</LeadTime>
            </ItemDetail>
            <SupplierID domain=&#34;DUNS&#34;>022878979</SupplierID>
        </ItemIn>
    </PunchOutOrderMessage>
</Message>
</cXML>
    */