<?php
namespace Vbw\Procurement\Punchout\Order;

require_once "Vbw/Procurement/Punchout/Data/Access.php";


use Vbw\Procurement\Punchout;

/**
$item = new $item;

$item->setQuantity();
$item->setSupplierId();
$item->setSupplierAuxId();

$item->setUnitPrice($price,$currency);
$item->setDescription($description,$language);
$item->setClassificication($class,$domain);
$item->setUom();

$item->setSomeRandom("");
 */

class Item
    extends Punchout\Data\Access
{

    protected $_quantity = 0;

    protected $_supplierId = null;

    protected $_supplierAuxId = null;

    protected $_unitPrice = 0;

    protected $_currency = "USD";

    protected $_classification = null;

    protected $_classDomain = null;

    protected $_uom = "EA";

    protected $_description = "";

    protected $_language = "en";

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
                switch (strtolower($key))  {
                    default :
                        $this->{"set". $key}($value);
                        break;
                }
            }
        }
    }

    public function toArray ()
    {
        $data = parent::toArray();
        $params = array (
            "quantity","supplierid","supplierauxid","unitprice","currency",
            "classification","classdomain","uom","description","language"
        );
        foreach ($params AS $idx=>$key) {
            $data[$key] = $this->{"get". ucfirst($key)}();
        }
        return $data;
    }

    public function setClassDomain($classDomain)
    {
        $this->_classDomain = $classDomain;
    }

    public function getClassDomain()
    {
        return $this->_classDomain;
    }

    public function setClassification($classification)
    {
        $this->_classification = $classification;
    }

    public function getClassification()
    {
        return $this->_classification;
    }

    public function setCurrency($currency)
    {
        $this->_currency = $currency;
    }

    public function getCurrency()
    {
        return $this->_currency;
    }

    public function setQuantity($quantity)
    {
        $this->_quantity = $quantity;
    }

    public function getQuantity()
    {
        return $this->_quantity;
    }

    public function setSupplierAuxId($supplierAuxId)
    {
        $this->_supplierAuxId = $supplierAuxId;
    }

    public function getSupplierAuxId()
    {
        return $this->_supplierAuxId;
    }

    public function setSupplierId($supplierId)
    {
        $this->_supplierId = $supplierId;
    }

    public function getSupplierId()
    {
        return $this->_supplierId;
    }

    public function setUnitPrice($unitPrice,$currency = null)
    {
        $this->_unitPrice = $unitPrice;
        if ($currency != null) {
            $this->setCurrency($currency);
        }
    }

    public function getUnitPrice()
    {
        return $this->_unitPrice;
    }

    public function setUom($uom)
    {
        $this->_uom = $uom;
    }

    public function getUom()
    {
        return $this->_uom;
    }

    public function setDescription($description,$language = null)
    {
        $this->_description = $description;
        if ($language != null)  {
            $this->setLanguage($language);
        }
    }

    public function getDescription()
    {
        return $this->_description;
    }

    public function setLanguage($language)
    {
        $this->_language = $language;
    }

    public function getLanguage()
    {
        return $this->_language;
    }



/*
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
    */
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