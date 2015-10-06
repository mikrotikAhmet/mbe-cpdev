<?php

class Mbemro_Customer_Model_Observer {

    public function __construct()   {    }

    public function customer_register_success($observer)
    {
        $customer = $observer->getCustomer();
        
        $templateId = 1;
        // Set sender information

        $senderName = Mage::getStoreConfig('trans_email/ident_custom1/name');
        $senderEmail = Mage::getStoreConfig('trans_email/ident_custom1/email');    
        $sender = array('name' => $senderName,
                        'email' => $senderEmail);

        $recepientEmail = Mage::getStoreConfig('sales_email/order/copy_to');
        if (strpos($recepientEmail, ',') !== false) {
            $recepientEmail = explode(',', $recepientEmail);
        }
         
        // Set recepient information
        //$recepientEmail = 'orders@mbemrocatalog.com';
        //$recepientEmail = 'sosingus@gmail.com';
        $recepientName = null;
         
        // Get Store ID    
        $storeId = Mage::app()->getStore()->getId();

        // Set variables that can be used in email template
        // $vars = array('customerName' => 'customer@example.com',
        //       'customerEmail' => 'Mr. Nil Cust');
        $vars = array(
                        'customer'       => $customer, 
                        'firstName'      => $customer->getFirstname(),
                        'lastName'       => $customer->getLastname(),
                        'company'        => $customer->getCorpCompany(),
                        'department'     => $customer->getCorpDepartment(),
                        'customerNumber' => $customer->getId(),
              );
             
        $translate  = Mage::getSingleton('core/translate');
 
        // Send Transactional Email
        Mage::getModel('core/email_template')
            ->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $storeId);
                 
        $translate->setTranslateInline(true); 

    }

    public function customer_address_added($observer)
    {
        $address = $observer->getAddress();
        $customer = $observer->getCustomer();
        
        $templateId = 2;
        // Set sender information          
        $senderName = Mage::getStoreConfig('trans_email/ident_custom1/name');
        $senderEmail = Mage::getStoreConfig('trans_email/ident_custom1/email');    
        $sender = array('name' => $senderName,
                    'email' => $senderEmail);

        $recepientEmail = Mage::getStoreConfig('sales_email/order/copy_to');
        if (strpos($recepientEmail, ',') !== false) {
            $recepientEmail = explode(',', $recepientEmail);
        }

        // Set recepient information
        //$recepientEmail = 'sosingus@gmail.com';
        $recepientName = null;
         
        // Get Store ID    
        $storeId = Mage::app()->getStore()->getId();

        // Set variables that can be used in email template
        // $vars = array('customerName' => 'customer@example.com',
        //       'customerEmail' => 'Mr. Nil Cust');

        $vars = $this->getAddressVars($address);
        $vars['customer'] = $customer;
        $vars['firstName'] = $customer->getFirstname();
        $vars['lastName'] = $customer->getLastname();
                                     
        $translate  = Mage::getSingleton('core/translate');
 
        // Send Transactional Email
        Mage::getModel('core/email_template')
            ->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $storeId);
                 
        $translate->setTranslateInline(true);

    }

    public function customer_address_saved($observer)
    {
        $address = $observer->getAddress();
        $oldAddress = $observer->getOldaddress();
        $customer = $observer->getCustomer();
        
        $templateId = 3;
        // Set sender information          
        $senderName = Mage::getStoreConfig('trans_email/ident_custom1/name');
        $senderEmail = Mage::getStoreConfig('trans_email/ident_custom1/email');    
        $sender = array('name' => $senderName,
                    'email' => $senderEmail);

        $recepientEmail = Mage::getStoreConfig('sales_email/order/copy_to');
        if (strpos($recepientEmail, ',') !== false) {
            $recepientEmail = explode(',', $recepientEmail);
        }

        // Set recepient information
        //$recepientEmail = 'sosingus@gmail.com';
        $recepientName = null;
         
        // Get Store ID    
        $storeId = Mage::app()->getStore()->getId();

        // Set variables that can be used in email template
        // $vars = array('customerName' => 'customer@example.com',
        //       'customerEmail' => 'Mr. Nil Cust');

        $vars = $this->getAddressVars($address);
        $vars['customer'] = $customer;
        $vars['firstName'] = $customer->getFirstname();
        $vars['lastName'] = $customer->getLastname();
        $vars = array_merge($vars, $this->getAddressVars($oldAddress, 'old'));
             
        $translate  = Mage::getSingleton('core/translate');
 
        // Send Transactional Email
        Mage::getModel('core/email_template')
            ->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $storeId);
                 
        $translate->setTranslateInline(true); 

    }

    public function customer_address_removed($observer)
    {
        $address = $observer->getAddress();
        $customer = $observer->getCustomer();
        
        $templateId = 4;
        // Set sender information          
        $senderName = Mage::getStoreConfig('trans_email/ident_custom1/name');
        $senderEmail = Mage::getStoreConfig('trans_email/ident_custom1/email');    
        $sender = array('name' => $senderName,
                    'email' => $senderEmail);

        $recepientEmail = Mage::getStoreConfig('sales_email/order/copy_to');
        if (strpos($recepientEmail, ',') !== false) {
            $recepientEmail = explode(',', $recepientEmail);
        }

        // Set recepient information
        //$recepientEmail = 'sosingus@gmail.com';
        $recepientName = null;
         
        // Get Store ID    
        $storeId = Mage::app()->getStore()->getId();

        // Set variables that can be used in email template
        // $vars = array('customerName' => 'customer@example.com',
        //       'customerEmail' => 'Mr. Nil Cust');

        $vars = $this->getAddressVars($address);
        $vars['customer'] = $customer;
        $vars['firstName'] = $customer->getFirstname();
        $vars['lastName'] = $customer->getLastname();

//        print_r($vars);
                                     
        $translate  = Mage::getSingleton('core/translate');
 
        // Send Transactional Email
        Mage::getModel('core/email_template')
            ->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $storeId);
                 
        $translate->setTranslateInline(true);
        
    }

    private function getAddressVars($address, $prefix = '')
    {
        $region = "";
        if ($address->getRegionId() != "") {
            $region = Mage::getModel('directory/region')->load($address->getRegionId())->getName();
        }
        
        $vars = array(
                        $prefix . 'address_firstName'   => $address->getFirstname(),
                        $prefix . 'address_lastName'   => $address->getLastname(),
                        $prefix . 'address_company'   => $address->getCompany(),
                        $prefix . 'address_phone'=> $address->getTelephone(),
                        $prefix . 'address_fax'=> $address->getFax(),
                        $prefix . 'address_country'=> $address->getCountryId(),
                        $prefix . 'address_zip'=> $address->getPostcode(),
                        $prefix . 'address_city'=> $address->getCity(),
                        $prefix . 'address_region'=> $region,
                        $prefix . 'address_line1'=> $address->getStreet1(),
                        $prefix . 'address_line2'=> $address->getStreet2(),
              );               

        return $vars; 
    }

    
 }
