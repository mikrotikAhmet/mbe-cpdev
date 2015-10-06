<?php



class Vbw_Punchout_Helper_Config
	extends Mage_Core_Helper_Url
{

    /**
     * @var array
     */
    protected $_customMap = null;

    /**
     * Returns a configuration value based on an xpath
     *
     * @param string $xpath location to config.
     * @return mixed
     */
    public function getConfig($xpath)
    {
        $xpath = "vbw_punchout/". $xpath;
        return Mage::getStoreConfig($xpath);
    }

    /**
     * is the store punchout enabled.
     *
     * @return boolean
     */
    public function isPunchoutEnabled()
    {
        $enabled = $this->getConfig('site/punchout_enabled');
        if ($enabled == 1) {
            return true;
        }
        return false;
    }

    /**
     * is punchout only
     *
     * @return boolean
     */
    public function isPunchoutOnly ()
    {
        $only = $this->getConfig('site/punchout_only');
        if ($only == 1) {
            return true;
        }
        return false;
    }

    /**
     * gets the field name for UNSPSC classificiations
     *
     * @return mixed
     */
    public function getProductUnspscField ()
    {
        return $this->getConfig('catalog_fields/product_unspsc');
    }

    /**
     * gets the field name for Unit of Measure classificiations
     *
     * @return mixed
     */
    public function getProductUomField ()
    {
        return $this->getConfig('catalog_fields/product_uom');
    }

    /**
     * gets the field name for UNSPSC classificiations
     *
     * @return mixed
     */
    public function getCategoryUnspscField ()
    {
        return $this->getConfig('catalog_fields/category_unspsc');
    }

    /**
     * gets the field name for punchout exporting
     *
     * @return mixed
     */
    public function getCategoryPunchoutExportField ()
    {
        return $this->getConfig('catalog_fields/category_export');
    }


    /**
     * get the type of login used with the session.
     *
     * @return string
     */
    public function getSessionLoginType ()
    {
        return $this->getConfig('site/session_type');
    }

    /**
     * anonymous means no user is actually logged in.
     *
     * @return boolean
     */
    public function isAnonymousSession ()
    {
        $sessionType = $this->getSessionLoginType();
        if ($sessionType == Vbw_Punchout_Model_Session::SESSION_TYPE_ANONYMOUS) {
            return true;
        }
        return false;
    }

    /**
     * single means that a pre-designed user (of a group) is automatically signed in.
     *
     * @return boolean
     */
    public function isSingleLoginSession ()
    {
        $sessionType = $this->getSessionLoginType();
        if ($sessionType == Vbw_Punchout_Model_Session::SESSION_TYPE_SINGLE) {
            return true;
        }
        return false;
    }

    /**
     * dual means that the user can register and have their own account, they are automatically
     * subscribed as a specific group.
     *
     * this is no longer supported.
     *
     * @return boolean
     */
    public function isDualLoginSession ()
    {
        $sessionType = $this->getSessionLoginType();
        if ($sessionType == Vbw_Punchout_Model_Session::SESSION_TYPE_DUAL) {
            return true;
        }
        return false;
    }

    /**
     *
     *
     * @return boolean
     */
    public function isDiscoverLoginSession ()
    {
        $sessionType = $this->getSessionLoginType();
        if ($sessionType == Vbw_Punchout_Model_Session::SESSION_TYPE_DISCOVER) {
            return true;
        }
        return false;
    }


    /**
     * this is not used.
    public function allowClassificationCascading ()
    {
        return $this->getConfig('catalog/product/classification_cascading');
    }
    */


    /**
     * get the appropriate login id for this store's single login session.
     * @todo adding {request} hanlde to pull the ID from embeded data in the
     * @todo originating punchout request.
     *
     * @return string
     */
    public function getSingleLoginUser ()
    {
        return $this->getConfig('site/single_login_user');
    }

    /**
     * get the appropriate login id for this store's single login session.
     *
     *
     * @return string
     */
    public function getSingleLoginGroup ()
    {
        return $this->getConfig('site/single_login_group');
    }

    /**
     * get the appropriate group id for this store's anonymous session.
     *
     * @return string
     */
    public function getAnonymousLoginGroup ()
    {
        return $this->getConfig('site/anonymous_login_group');
    }


    /**
     * dual login no longer used.
     *
     * @return string
     */
    public function getDualLoginGroup ()
    {
        return $this->getConfig('site/dual_login_group');
    }


    /**
     * @param array $customMap
     */
    public function setCustomMap($customMap)
    {
        $this->_customMap = $customMap;
    }

    /**
     * @return array
     */
    public function getCustomMap()
    {
        if ($this->_customMap === null) {
            $map = unserialize($this->getConfig('order/lineitem_mapping'));
            if (is_array($map)) {
                $this->_customMap = $map;
            } else {
                $this->_customMap = array();

            }
        }
        return $this->_customMap;
    }

    /**
     * @return array
     */
    public function getErrorMap ()
    {
        $maps = array();
        $errors = unserialize($this->getConfig('display/error_mapping'));
        if (!empty($errors)
            && is_array($errors)) {
            foreach ($errors AS $map) {
                if (isset($map['error'])
                        && isset($map['cms'])) {
                    $maps[] = array (
                        'error' => $map['error'],
                        'cms' => $map['cms']
                    );
                }
            }
        }
        return $maps;
    }

    /**
     * @param $error_code
     * @return string
     */
    public function matchErrorMap ($error_code)
    {
        $errors = $this->getErrorMap();
        foreach ($errors AS $err) {
            if (preg_match('/^'. $err['error'] .'$/',$error_code)) {
                $cms_page = $err['cms'];
                /** @var $pages Mage_Cms_Model_Resource_Page_Collection */
                $pages = Mage::getModel('cms/page')->getCollection();
                $pages->addFieldToFilter('is_active',1);
                $pages->addFieldToFilter('identifier',$cms_page);
                $pages->addStoreFilter(Mage::app()->getStore()->getId());
                if ($pages->count() > 0) {
                    $ids = $pages->getAllIds();
                    return $ids[0];
                }
            }
        }
        return null;
    }

    /**
     * @return int : 1 = Yes, In Top Links, 2 = Yes, Custom, 3 = No
     */
    public function useReturnLink(){
        return $this->getConfig('display/show_return');
    }

}
	