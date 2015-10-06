<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Sphinx Search Ultimate
 * @version   2.3.1
 * @revision  675
 * @copyright Copyright (C) 2014 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_MstCore_Helper_Code extends Mage_Core_Helper_Data
{
    const LICENSE_URL = 'http://mirasvit.com/lc/check/';

    const STATUS_APPROVED = 'APPROVED';

    const EE_EDITION = 'EE';
    const PE_EDITION = 'PE';
    const CE_EDITION = 'CE';

    protected static $_edition = false;
    protected $o;
    protected $k;
    protected $s;
    protected $r;
    protected $v;

    public static function getEdition()
    {
        if (!self::$_edition) {
            $pathToClaim    = BP.DS."app".DS."etc".DS."modules".DS.'Enterprise'."_".'Enterprise'.".xml";
            $pathToEEConfig = BP.DS."app".DS."code".DS."core".DS.'Enterprise'.DS.'Enterprise'.DS."etc".DS."config.xml";
            $isCommunity    = !file_exists($pathToClaim) || !file_exists($pathToEEConfig);

            if ($isCommunity) {
                 self::$_edition = self::CE_EDITION;
            } else {
                $_xml = @simplexml_load_file($pathToEEConfig, 'SimpleXMLElement', LIBXML_NOCDATA);
                if(!$_xml === FALSE) {
                    $package = (string)$_xml->default->design->package->name;
                    $theme   = (string)$_xml->install->design->theme->default;
                    $skin    = (string)$_xml->stores->admin->design->theme->skin;

                    $isProffessional = ($package == "pro") && ($theme == "pro") && ($skin == "pro");

                    if ($isProffessional) {
                        self::$_edition = self::PE_EDITION;
                        return self::$_edition;
                    }
                }

                self::$_edition = self::EE_EDITION;
            }
        }

        return self::$_edition;
    }

    public function getOurExtensions()
    {
        $extensions = array();

        foreach (Mage::getConfig()->getNode('modules')->children() as $name => $module) {
            if ($module->active != 'true') {
                continue;
            }
            if (strpos($name, 'Mirasvit_') === 0) {
                if ($name == 'Mirasvit_MstCore' || $name == 'Mirasvit_MCore') {
                    continue;
                }

                $parts = explode('_', $name);

                if ($helper = $this->getCodeHelper($parts[1])) {
                    if (method_exists($helper, 'getSku')
                        && method_exists($helper, 'getOrderId')
                        && method_exists($helper, 'getVersion')
                        && method_exists($helper, 'getRevision')
                        && method_exists($helper, 'getLicenseKey')) {
                        $extensions[] = array(
                            's' => $helper->getSku(),
                            'o' => $helper->getOrderId(),
                            'v' => $helper->getVersion(),
                            'r' => $helper->getRevision(),
                            'k' => $helper->getLicenseKey(),
                        );
                    }
                }
            }
        }

        return $extensions;
    }

    private function check()
    {
        if (time() - Mage::app()->loadCache(md5(self::LICENSE_URL)) > 24 * 60 * 60) {
            $this->refresh();
        }
    }

    public function getCodeHelper($extensionName)
    {
        $file = Mage::getBaseDir().'/app/code/local/Mirasvit/'.$extensionName.'/Helper/Code.php';
        if (file_exists($file)) {
            $helper = Mage::helper(strtolower($extensionName).'/code');
            return $helper;
        }
        return false;
    }

    public function getCodeHelper2($extensionName)
    {
        foreach (Mage::getConfig()->getNode('modules')->children() as $name => $module) {
            if (strtolower('Mirasvit_'.$extensionName) === strtolower($name)
                || $extensionName == $name) {
                $parts = explode('_', $name);
                if (isset($parts[1])) {
                    $helper = $this->getCodeHelper($parts[1]);
                    return $helper;
                }
            }
        }
        return false;
    }

    private function refresh()
    {
        $params       = array();
        $params['v']  = 2;
        $params['d']  = Mage::getBaseUrl();
        $params['ip'] = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
        $params['mv'] = Mage::getVersion();
        $params['me'] = self::getEdition();
        $extensions   = $this->getOurExtensions();
        $keys         = array();

        $params['p'] = serialize($extensions);

        try {
            Mage::app()->saveCache(time(), md5(self::LICENSE_URL));

            $result = $this->getResponce(self::LICENSE_URL, $params);
            if (!$result || $result == '') {
                return $this;
            }

            $result   = base64_decode($result);
            $xml      = simplexml_load_string($result);
            $products = array();

            try {
                if ($record = Mage::getStoreConfig('mstcore/system/status')) {
                   $products = str_rot13(base64_decode(@unserialize($record)));
                }
            } catch (Exception $ex) {}

            foreach ($xml->products->product as $product) {
                $products[(string)$product->sku] = array(
            		'status'  => (string) $product->status,
                    'message' => (string) $product->message
                );
            }
            $this->saveConfig('mstcore/system/status', base64_encode(str_rot13(serialize($products))));
        } catch (Exception $ex) {}

        return $this;
    }

    private function checkLicense()
    {
        $sku = $this->getSku();
        $this->check();
        if (!$record = Mage::getStoreConfig('mstcore/system/status')) {
            return self::STATUS_APPROVED;
        }
        try {
            $products = @unserialize(str_rot13(base64_decode($record)));
        } catch (Exception $ex) {}

        if(isset($products[$sku])) {
            $record = $products[$sku];
            if ($record['status'] == 'BANNED') {
                return $record['message'];
            }
        }
        return self::STATUS_APPROVED;
    }

    private function saveConfig($path, $value, $scope = 'default', $scopeId = 0)
    {
        $resource = Mage::getResourceModel('core/config');
        $resource->saveConfig(rtrim($path, '/'), $value, $scope, $scopeId);
        return $this;
    }

    private function getResponce($url, $params)
    {
        $curl = new Varien_Http_Adapter_Curl();
        $curl->write(Zend_Http_Client::POST, $url, '1.1', array(), http_build_query($params, '', '&'));
        $data = $curl->read();
        $data = preg_split('/^\r?$/m', $data, 2);
        $data = trim($data[1]);
        return $data;
    }


    /**
     * Ð²ÑÐ·Ð¾Ð² ÑÐ¾Ð»ÑÐºÐ¾ Ð¸Ð· Ð½Ð°ÑÐ»ÐµÐ´ÑÐµÐ¼Ð¾Ð³Ð¾ ÐºÐ»Ð°ÑÑÐ°
     */
    public function checkLicenseKeyAdmin($ob)
    {
        $result = $this->checkLicense();
        if ($result === self::STATUS_APPROVED) {
            return true;
        }
        $session = Mage::getSingleton('adminhtml/session');
        $session->addError(Mage::helper('mstcore')->__($result));
        $url = Mage::helper('adminhtml')->getUrl('adminhtml');
        Mage::app()->getResponse()->setRedirect($url);
        return false;
    }

    /**
     * Ð²ÑÐ·Ð¾Ð² ÑÐ¾Ð»ÑÐºÐ¾ Ð¸Ð· Ð½Ð°ÑÐ»ÐµÐ´ÑÐµÐ¼Ð¾Ð³Ð¾ ÐºÐ»Ð°ÑÑÐ°
     */
    public function checkLicenseKey()
    {
        $result = $this->checkLicense();
        if ($result === self::STATUS_APPROVED) {
            return true;
        }
        return false;
    }

    /**
     * Ð²ÑÐ·Ð¾Ð² ÑÐ¾Ð»ÑÐºÐ¾ Ð¸Ð· Ð½Ð°ÑÐ»ÐµÐ´ÑÐµÐ¼Ð¾Ð³Ð¾ ÐºÐ»Ð°ÑÑÐ°
     */
    public function checkConfig()
    {
        $result = $this->checkLicense();
        if ($result === self::STATUS_APPROVED) {
            return true;
        }
        $session = Mage::getSingleton('adminhtml/session');
        $session->addError(Mage::helper('mstcore')->__($result));
        return true;
    }

    public function getStatus()
    {
        try {
            if ($record = Mage::getStoreConfig('mstcore/system/status')) {
               $products = @unserialize(str_rot13(base64_decode(($record))));
               foreach ($products as $product) {
                   if ($product['status'] == 'BANNED') {
                       return false;
                   }
               }
            }
        } catch (Exception $e) {}

        return true;
    }

    protected function getLicenseKey()
    {
        return $this->k;
    }

    protected function getSku()
    {
        return $this->s;
    }

    protected function getOrderId()
    {
        return $this->o;
    }

    protected function getVersion()
    {
        return $this->v;
    }

    protected function getRevision()
    {
        return $this->r;
    }
}
