<?php
namespace Vbw\Procurement\Punchout\Request\Body;

use Vbw\Procurement\Punchout;

class Shipping
    extends Punchout\Data\Access
{



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