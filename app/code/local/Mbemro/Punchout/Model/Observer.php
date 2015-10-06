<?php

class Mbemro_Punchout_Model_Observer
{
	public function autologin()
	{
		$website = Mage::app()->getWebsite();
		if($website->getCode() == 'gm') {
			$punchoutSession = Mage::getSingleton('vbw_punchout/session');
		        $isPunchout = $punchoutSession->isPunchoutSession();
			if(!$isPunchout && !$this->isAllowed()) {
				return;
			}
			try {
				$userSession = Mage::getSingleton('customer/session');
				if (!$userSession->isLoggedin()) {
					$customer = Mage::getModel("customer/customer");
					$customer->setWebsiteId( $website->getId() );
//					$customer->loadByEmail("user@gm.com");
					$customer->loadByEmail("sofi@cp-dev.com");
					if($customer->getId()) {
						$userSession->setCustomer($customer);
						Mage::dispatchEvent('customer_login', array('customer'=>$customer));
					}
				}
			} catch (Exception $e) {
				Mage::logException($e);
			}
		}
	}

	public function checkAllowed($event)
	{
		if($this->isAllowed()) {
			$event->result->setShouldProceed(false);
		}
	}
	
	private $allowList = array('198.11.244.242', '127.0.0.1', '10.108.235.130', /* '95.180.60.81'*/);
	private function isAllowed()
	{
		$ips = $this->processRemoteIp($_SERVER['HTTP_X_FORWARDED_FOR']);
		return 
			in_array($ips[0], $this->allowList) 
			|| (php_sapi_name() == 'cgi-fcgi') 
//			|| $this->ipSaved($ips[0])
			|| $this->basicAuth($ips[0]);
	}

	private function basicAuth($ip)
	{
		if(isset($_SERVER['PHP_AUTH_USER']) && $this->validateBasicAuth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $ip)) {
                        return true;
                }

		header('WWW-Authenticate: Basic realm="MBE GM Website"');
		header('HTTP/1.0 401 Unauthorized');
		echo 'Please authenticate to access this website.';
		exit;

	}

	private $users = array(
		'ratko' => 'development',
		'mivan' => 'development',
		'sofi'  => 'development',
		'development' => 'development',
	);

	private function getIPDir()
	{
		return __DIR__ . '/../misc/';
	}

	private function validateBasicAuth($username, $password, $ip)
	{
		if(isset($this->users[$username]) && ($this->users[$username] == $password)) {
			$filename = $this->getIPDir() . $username . '.ip';
			file_put_contents($filename, $ip);
			return true;
		}

		return false;
	}

	private function processRemoteIp($ip_holder)
	{
		if(strpos($ip_holder, ',') === false) {
			return array($ip_holder);
		}
		$ips = explode(',', $ip_holder);
		foreach($ips as &$ip) {
			$ip = trim($ip);
		}

		return $ips;
	}

	private function ipSaved($ip)
	{
		$files = glob($this->getIPDir() . '*.ip');
		foreach($files as $file) {
			$saved_ip = trim(file_get_contents($file));
			if($saved_ip == $ip) {
				return true;
			}
		}

		return false;
	}

}
