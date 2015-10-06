<?php

include_once(Mage::getBaseDir('lib') . '/PHPExcel.php' );

class Mbemro_CustomerReports_Helper_Customerreports extends Mage_Core_Helper_Abstract
{

	public function getMyPurchasesCollection($filterObj = null) {
        
        //$categoryId = (int) $this->getRequest()->getParam('category_id');
        
        $session = Mage::getSingleton('customer/session');
        /** var $customer Mage_Customer_Model_Customer */
        $customer = $session->getCustomer();
        $customerId = $customer->getId();
        
        $corp_company = Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter('corp_company_dd')->getFirstItem();
		$corp_department = Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter('corp_department_dd')->getFirstItem();

/* Currently sql for all purchases, should be filtered by customer for live */
        $sql = "select
                -- o.entity_id,
                -- o.store_id,
                concat(addr.region, ', ', addr.city, ', ', addr.street) Address,
                cev.value Category,
                o.grand_total,
                cast(o.created_at as date) order_date,
                -- cv.value customer,
                -- p.sku,
                ceov.value Department
                -- p.name Product
                -- ce.entity_id
                from sales_flat_order o
                inner join sales_flat_order_address addr on addr.parent_id = o.entity_id
                inner join customer_entity_varchar cv on cv.entity_id = o.customer_id
                inner join sales_flat_order_item p on p.order_id = o.entity_id
                inner join catalog_category_product cp on cp.product_id= p.product_id
                inner join catalog_category_entity_varchar cev on cev.entity_id = cp.category_id
                inner join catalog_category_entity ce on ce.entity_id = cp.category_id
                -- LEFT JOIN customer_entity_int cev1 ON cev1.entity_id = o.customer_id AND cev1.attribute_id = " . $corp_company->getId() ."
				LEFT JOIN customer_entity_int cev2 ON cev2.entity_id = o.customer_id AND cev2.attribute_id = ".$corp_department->getId()." 
				-- LEFT JOIN eav_attribute_option ceo ON ceo.attribute_id = ".$corp_department->getId()."
				LEFT JOIN eav_attribute_option_value ceov ON ceov.option_id = cev2.value and ceov.store_id=0
				
                where /*o.status = 'complete' and*/ cv.attribute_id = 5 /*in (5,6,7)*/
                  and cev.entity_type_id = 3 and cev.attribute_id = 41
                  and ce.parent_id = 2
                ";
        //if supervisor, get all accounts under same company
        $accounts = $this->getSupervisedAccounts($customer);
        if (!empty($accounts)) {
        	$sql .= " and ((o.customer_id = $customerId) or (o.customer_id in (" . implode(",", $accounts) . ") ))";
        } else {
        	$sql .= " and (o.customer_id = $customerId)";
        }

		$readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        
        if (!is_null($filterObj)) {
        	if ($filterObj->time_limit === 2) {
        		$sql .=  " and cast(o.created_at as date) >= " . $readConnection->quote($filterObj->time_limit_start)
        		         . " and cast(o.created_at as date) <=" . $readConnection->quote($filterObj->time_limit_end)
        		         ;
        	}
        	if (($filterObj->orders_all === 2) && is_array($filterObj->order_choices)) {
        		
        		foreach ($filterObj->order_choices as &$order_status) {
        			$order_status = $readConnection->quote($order_status);
        		}
        		$sql .= " and o.status in (" . implode(",", $filterObj->order_choices) . ")";
        	}
        }

        return $readConnection->fetchAll($sql);
        
        // $this->getResponse()->setHeader('Content-type', 'application/json');
        // $this->getResponse()->setBody($jsonData);
    }

    public function getItemCount() 
    {
    	return 1; //to do
    }

    private function getSupervisedAccounts($_customer) 
    {
    	$custIds = array();
    	//if this is a supervisor account
    	if ($_customer->getCorpDepSupervisor()) {
    		$corp_company = Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter('corp_company_dd')->getFirstItem();
			$corp_department = Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter('corp_department_dd')->getFirstItem();		
			$resource = Mage::getSingleton('core/resource');
			$readConnection = $resource->getConnection('core_read');
			$query = "SELECT ce.entity_id, ce.email, cev.value AS company, cev1.value AS department "
					."FROM customer_entity ce "
					."LEFT JOIN customer_entity_int cev ON ce.entity_id = cev.entity_id "
					."AND cev.attribute_id = ".$corp_company->getId()." "
					."LEFT JOIN customer_entity_int cev1 ON ce.entity_id = cev1.entity_id "
					."AND cev1.attribute_id = ".$corp_department->getId()." "
					."WHERE cev.value='".$_customer->getCorpCompanyDd()."' and cev1.value='".$_customer->getCorpDepartmentDd()."' ";
			$results = $readConnection->fetchAll($query);
			foreach($results as $row){
				$custIds[] = $row['entity_id'];
			}
    	}
    	return $custIds;
    }

    public function prepareColumns($items)
    {
    	$columns = array_keys($items[0]);
    	$out = array();
    	$rank = 1;
    	foreach ($columns as $column) {
    		
    		$col = array(
    			"colvalue" => $column,
    			"coltext"  => $column,
    			"header"   => $column,
    			"sortbycol"   => $column,
    			"groupbyrank"   => null,
    			"pivot"   => false,
    			"result"   => false
    			);

    		if (($column !== "grand_total") && ($column !== "order_date")) {
    			$col['groupbyrank'] = $rank;
    			$rank++;
    		} 

    		if (strtoupper("order_date") == strtoupper($column)) {
    			$col['pivot'] = "true";
    			$col['result'] = "true";
    		}

    		if (strtoupper("grand_total") == strtoupper($column)) {
    			$col['result'] = "true";
    		}
			
    		$out[] = $col;
    	}
    	return $out;
    /*	
    	$result = "\ncolumns: [\n";
    	foreach ($out as $column) {
    		$result .= "\t" . '{ colvalue: "' . $column['colvalue'] . '", coltext: "'.$column['coltext'] . '", header: "'. $column['header'] . '", sortbycol: "'.$column['sortbycol'].'", groupbyrank: '.$column['groupbyrank'].', pivot: '.$column['pivot'].', result: '.$column['result'].' },' . "\n";
    	}
	    $result = preg_replace('/,\n$/', "\n", $result) . "],\n";

    	return $result;
    */	
    }

    public function prepareResults($items)
    {
    	foreach ($items as &$item) {
    		$item['order_date'] = date("M-Y", strtotime($item['order_date']));
    		$item['grand_total'] = floatval($item['grand_total']);
    	}
    	$columns = $this->prepareColumns($items);
    	
    	$obj = new StdClass;
    	$obj->dataid = "Customer Order Reports";
    	$obj->columns = $columns;
    	$obj->rows = $items;
    	$response = json_encode($obj);
    	return $response;
    /*	
    	$out = "{\ndataid: \"Customer Order Reports\",\n" . $columns;
    	$out .= "rows: " . json_encode($items);
    	$out .= "\n}";
    	
    	return $out;
    */
    }

    public function getReportAsJson($filterObj)
    {
		$rows = $this->getMyPurchasesCollection($filterObj);
		return $this->prepareResults($rows);
    }

    private function formatDateShort(&$element, $key=-1, $format = "M-Y")
    {
    	$element = date($format, strtotime($element));
    }

    public function toXLS($items)
    {
    	$dates = array();

    	foreach ($items as $item) {
    		$dates[] = $item['order_date'];
    	}
    	sort($dates);
    	array_walk($dates, array($this, 'formatDateShort') , "M-Y");
    	$dates = array_unique($dates);
    	
    	$xls = new PHPExcel();
		$xls->getProperties()->setCreator("mbemrocatalog.com")
								 ->setTitle("My Purchases Report")
								 ->setSubject("My Purchases Report " + $this->formatDate(min($dates)) . "-" . $this->formatDate(max($dates)))
								 ->setDescription("")
								 ->setKeywords("")
								 ->setCategory("flights");
		$worksheet = $xls->getActiveSheet();
		
		$worksheet->setTitle('MBE MRO My Purchases Summary');
		$summaryItems = $this->sumarize($items);
		
		$this->xlsRenderItems($worksheet, $summaryItems, $dates);

		$worksheet = $xls->createSheet();
		$worksheet->setTitle('MBE MRO My Purchases Details');

		$this->xlsRenderItems($worksheet, $items, $dates);

		
		$objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel5');

		header('Content-type: application/vnd.ms-excel');
		header('Content-Disposition: attachment; filename="My_Purchases.xls"');
		$objWriter->save('php://output');
		
    }

    private function xlsRenderItems($worksheet, $items, $dates) {

		$worksheet->setCellValue('A1', 'Address');
		$worksheet->setCellValue('B1', 'Category');
		$worksheet->setCellValue('C1', 'Department');

		$startLetter = 'D';
		$dateLetter = array();
		foreach ($dates as $date) {
			$worksheet->setCellValue($startLetter . '1', date("M-Y", strtotime($date)) );
			#print "New letter $startLetter for" . date("M-Y", strtotime($date)) . "<br>";
			$dateLetter[date("M-Y", strtotime($date))] = $startLetter;
			$startLetter++;
		}
    	$i = 2;
		foreach ($items as $item) {
			$worksheet->setCellValue('A' . $i, $item['Address']);
			$worksheet->setCellValue('B' . $i, $item['Category']);
			$worksheet->setCellValue('C' . $i, $item['Department']);
			// print "order_date: " . $item['order_date'] . " / " . date("M-Y", strtotime($item['order_date'])) . "<br>\n";
			// print "Letter " . $dateLetter[date("M-Y", strtotime($item['order_date']))] . $i . " : " . $item['grand_total'] . "<br>\n";
			$worksheet->setCellValue($dateLetter[date("M-Y", strtotime($item['order_date']))] . $i, $item['grand_total']);
			$i++;
		}

		$startLetter--;

		$worksheet->getStyle("A1:$startLetter" ."1")->getFont()->setBold(true);

		$worksheet->setCellValue("A$i", "Totals");
		$worksheet->getStyle("A$i:$startLetter$i")->getFont()->setBold(true);

		$startLetter = 'D';
		$dateLetter = array();
		foreach ($dates as $date) {
			$formula = "=SUM($startLetter" . "2:$startLetter" . ($i-1) . ")";
			//print $formula . "<br>";
			$worksheet->setCellValue($startLetter . $i, $formula);
			$worksheet->getCell($startLetter . $i)->getCalculatedValue();
			$startLetter++;
		}

    }

    private function sumarize($items) {
    	$result = array();
    	foreach ($items as $item) {
    		$idx = $this->getItemIdx($result, $item);
    		if ($idx !== false) {
    			$result[$idx]['grand_total'] += $item['grand_total'];
    		} else {
    			$result[] = array(
    					'Address'=>$item['Address'],
    					'Category'=>$item['Category'],
    					'Department'=>$item['Department'],
    					'order_date'=>date("M-Y", strtotime($item['order_date'])),
    					'grand_total'=>$item['grand_total']
    				);
    		}
    	}
    	return $result;
    }

    private function getItemIdx($items, $comparingItem){
    	
    	foreach ($items as $key => $item) 
    	{
    // 		print "Comparing: {$item['Address']} == {$comparingItem['Address']}) ; {$item['Category']} == {$comparingItem['Category']} ; "
				// . "{$item['Department']}=={$comparingItem['Department']} ; <b>{$item['order_date']}==" . date("M-Y", strtotime($comparingItem['order_date'])) . "</b>\n";

    		if (($item['Address'] == $comparingItem['Address']) && ($item['Category'] == $comparingItem['Category']) 
    			&& ($item['Department']==$comparingItem['Department']) && ($item['order_date']==date("M-Y", strtotime($comparingItem['order_date'])))) 
    		{
    			return $key;
    		}
    	}
    	return false;

    }

    private function formatDate($dateString)
    {
    	return date("d M, yy", strtotime($dateString));
    }
	
}
