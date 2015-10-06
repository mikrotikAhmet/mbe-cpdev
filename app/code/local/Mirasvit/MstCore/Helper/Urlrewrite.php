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


class Mirasvit_MstCore_Helper_Urlrewrite extends Mage_Core_Helper_Data
{
	protected $config = array();
	protected $config2 = array();

	public function isEnabled($module)
	{
		if (!isset($this->config[$module])) {
			return false;
		}
		if (isset($this->config[$module]['_ENABLED'])) {
			return $this->config[$module]['_ENABLED'];
		}
		return true;
	}

	public function rewriteMode($module, $isEnabled)
	{
		$this->config[$module]['_ENABLED'] = $isEnabled;
	}

	public function registerBasePath($module, $path)
	{
		$this->config[$module]['_BASEPATH'] = $path;
		$this->config2[$path] = $module;
	}

	public function registerPath($module, $type, $pathTemplate, $action)
	{
		$this->config[$module][$type] = $pathTemplate;
		$this->config2[$module.'_'.$type] = $action;
	}

	public function getUniquePath($module, $type, $path, $objectId, $i = 0)
	{
		if ($i) {
			$pathToCheck = $path.'-'.$i;
		} else {
			$pathToCheck = $path;
		}
		// check path for dublicates
		$collection = Mage::getModel('mstcore/urlrewrite')->getCollection()
					->addFieldToFilter('module', $module)
					->addFieldToFilter('type', $type)
					->addFieldToFilter('url_key', $pathToCheck)
					->addFieldToFilter('entity_id', array('neq'=>$objectId))
					->setOrder('url_key', 'asc')
					;

		if ($collection->count()) {
			return $this->getUniquePath($module, $type, $path, $objectId, ++$i);
		}
		return $pathToCheck;
	}

	public function updateUrlrewrite($module, $type, $object, $values)
	{
		if (!isset($this->config[$module])) {
			return false;
		}
		$objectId = $object->getId();
		$pathTemplate = $this->config[$module][$type];
		$path = $pathTemplate;
		foreach ($values as $key => $value) {
			$path = str_replace("[$key]", $value, $path);
		}
		$path = trim($path, '/');
		$path = $this->getUniquePath($module, $type, $path, $objectId);

		$collection = Mage::getModel('mstcore/urlrewrite')->getCollection()
						->addFieldToFilter('module', $module)
						->addFieldToFilter('type', $type)
						->addFieldToFilter('entity_id', $objectId)
						;
		if ($collection->count()) {
			$rewrite = $collection->getFirstItem();
			$rewrite->setUrlKey($path)
					->save();
		} else {
			$rewrite = Mage::getModel('mstcore/urlrewrite');
			$rewrite
				->setModule($module)
				->setType($type)
				->setEntityId($objectId)
				->setUrlKey($path)
				->save();
		}
	}

	public function parseKeyNum($key)
	{
		preg_match('/(\d+)$/', $key, $matches);
		$result = 0;
		if (count($matches)) {
			$result = (int)$matches[1];
		}
		return $result;
	}

	public function removeUrlRewrite($module, $type, $object)
	{
		$collection = Mage::getModel('mstcore/urlrewrite')->getCollection()
						->addFieldToFilter('module', $module)
						->addFieldToFilter('type', $type)
						->addFieldToFilter('entity_id', $object->getId())
						;
		if ($collection->count()) {
			$rewrite = $collection->getFirstItem();
			$rewrite->delete();
		}
	}

	public function getUrl($module, $type, $object = false)
	{
		if ($this->isEnabled($module)) {
			$basePath = $this->config[$module]['_BASEPATH'];
			if ($object) {
				$collection = Mage::getModel('mstcore/urlrewrite')->getCollection()
						->addFieldToFilter('module', $module)
						->addFieldToFilter('type', $type)
						->addFieldToFilter('entity_id', $object->getId())
						;
				if ($collection->count()) {
					$rewrite = $collection->getFirstItem();
					return $this->getUrlByKey($basePath, $rewrite->getUrlKey());
				} else {
					return $this->getDefaultUrl($module, $type, $object);
				}
			} else {
				return $this->getUrlByKey($basePath, $this->config[$module][$type]);
			}
		} else {
			return $this->getDefaultUrl($module, $type, $object);
		}
	}

	protected function getDefaultUrl($module, $type, $object) {
			$action = $this->config2[$module.'_'.$type];
			$action = str_replace('_', '/', $action);
			$params = array();
			if ($object) {
				$params['id'] = $object->getId();
			}
			return Mage::getUrl($action, $params);
	}

	public function getUrlByKey($basePath, $urlKey, $params = false) {
		if ($urlKey) {
			$url = $basePath.'/'. $urlKey;
		} else {
			$url = $basePath;
		}
        $configUrlSuffix = Mage::getStoreConfig('catalog/seo/product_url_suffix');
        //user can enter .html or html suffix
        if ($configUrlSuffix != '' && $configUrlSuffix[0] != '.') {
            $configUrlSuffix = '.'.$configUrlSuffix;
        }
        if (substr($url, -strlen($configUrlSuffix)) == $configUrlSuffix) {
            $url = substr($url, 0, -strlen($configUrlSuffix));
        }
        $url .= $configUrlSuffix;
        if ($params) {
            $url .= '?'.http_build_query($params);
        }
        $url = Mage::getModel('core/url')->getDirectUrl($url);
		return $url;
	}

	public function getUrlKeyWithoutSuffix($key) {
        $configUrlSuffix = Mage::getStoreConfig('catalog/seo/product_url_suffix');
        //user can enter .html or html suffix
        if ($configUrlSuffix != '' && $configUrlSuffix[0] != '.') {
            $configUrlSuffix = '.'.$configUrlSuffix;
        }
        $key = str_replace($configUrlSuffix, '', $key);;
        return $key;
	}

	public function match($pathInfo) {

        $identifier = trim($pathInfo, '/');
        $parts      = explode('/', $identifier);
        if (count($parts) == 1) {
        	$parts[0] = $this->getUrlKeyWithoutSuffix($parts[0]);
        }

        if (isset($parts[0]) && !isset($this->config2[$parts[0]])) {
        	return false;
        }
        $module = $this->config2[$parts[0]];

		if (!$this->isEnabled($module)) {
			return false;
		}
		if (count($parts) > 1) {
	        unset($parts[0]);
	        $urlKey = implode('/', $parts);
	        $urlKey = $this->getUrlKeyWithoutSuffix($urlKey);
	    } else {
	    	$urlKey = '';
	    }

        //Ð¿ÑÐ¾Ð²ÐµÑÑÐµÐ¼ Ð½Ð° ÑÑÐ°ÑÐ¸ÑÐµÑÐºÐ¸Ðµ ÑÑÐ»Ñ (ÑÑÐ»Ñ Ð¿Ð¾ÑÑÐ¾ÑÐ½Ð½ÑÑ ÑÑÑÐ°Ð½Ð¸Ñ, Ð½Ð°Ð¿ÑÐ¸Ð¼ÐµÑ ÑÐ¿Ð¸ÑÐºÐ¾Ð²)
        $type = $rewrite = false;
        foreach ($this->config[$module] as $t => $key) {
        	if ($key === $urlKey) {
        		$type = $t;
        		break;
        	}
        }
        // Ð¿ÑÐ¾Ð²ÐµÑÑÐµÐ¼ Ð½Ð° Ð´Ð¸Ð½Ð°Ð¼Ð¸ÑÐµÑÐºÐ¸Ðµ ÑÑÐ»Ñ (ÑÑÐ»Ñ Ð¿ÑÐ¾Ð´ÑÐºÑÐ¾Ð², ÐºÐ°ÑÐµÐ³Ð¾ÑÐ¸Ð¹ Ð¸ ÑÐ´)
        if (!$type) {
			$collection = Mage::getModel('mstcore/urlrewrite')->getCollection()
					->addFieldToFilter('url_key', $urlKey)
					->addFieldToFilter('module', $module)
					;
			if ($collection->count()) {
				$rewrite = $collection->getFirstItem();
				$type = $rewrite->getType();
			} else {
				return false;
			}
		}
		if ($type) {
			$action = $this->config2[$module.'_'.$type];
			$result = new Varien_Object();
			$actionParts = explode('_', $action);
			$result->setRouteName($actionParts[0])
				->setModuleName($actionParts[0])
                ->setControllerName($actionParts[1])
                ->setActionName($actionParts[2])
                ;
             if ($rewrite) {
             	$result->setEntityId($rewrite->getEntityId());
             }
             return $result;
         }
         return false;
	}

   /**
	 * normalize Characters
	 * Example: Ã¼ -> ue
	 *
	 * @param string $string
	 * @return string
	 */
	public function normalize($string)
	{
	    $table = array(
	        'Å '=>'S',  'Å¡'=>'s',  'Ä'=>'Dj', 'Ä'=>'dj', 'Å½'=>'Z',  'Å¾'=>'z',  'Ä'=>'C',  'Ä'=>'c',  'Ä'=>'C',  'Ä'=>'c',
	        'Ã'=>'A',  'Ã'=>'A',  'Ã'=>'A',  'Ã'=>'A',  'Ã'=>'Ae', 'Ã'=>'A',  'Ã'=>'A',  'Ã'=>'C',  'Ã'=>'E',  'Ã'=>'E',
	        'Ã'=>'E',  'Ã'=>'E',  'Ã'=>'I',  'Ã'=>'I',  'Ã'=>'I',  'Ã'=>'I',  'Ã'=>'N',  'Ã'=>'O',  'Ã'=>'O',  'Ã'=>'O',
	        'Ã'=>'O',  'Ã'=>'Oe', 'Ã'=>'O',  'Ã'=>'U',  'Ã'=>'U',  'Ã'=>'U',  'Ã'=>'Ue', 'Ã'=>'Y',  'Ã'=>'B',  'Ã'=>'Ss',
	        'Ã '=>'a',  'Ã¡'=>'a',  'Ã¢'=>'a',  'Ã£'=>'a',  'Ã¤'=>'ae', 'Ã¥'=>'a',  'Ã¦'=>'a',  'Ã§'=>'c',  'Ã¨'=>'e',  'Ã©'=>'e',
	        'Ãª'=>'e',  'Ã«'=>'e',  'Ã¬'=>'i',  'Ã­'=>'i',  'Ã®'=>'i',  'Ã¯'=>'i',  'Ã°'=>'o',  'Ã±'=>'n',  'Ã²'=>'o',  'Ã³'=>'o',
	        'Ã´'=>'o',  'Ãµ'=>'o',  'Ã¶'=>'oe', 'Ã¸'=>'o',  'Ã¹'=>'u',  'Ãº'=>'u',  'Ã»'=>'u',  'Ã½'=>'y',  'Ã½'=>'y',  'Ã¾'=>'b',
	        'Ã¿'=>'y',  'Å'=>'R',  'Å'=>'r',  'Ã¼'=>'ue', '/'=>'',   '&'=>'',  '('=>'',   ')'=>''
	    );

	    $string = strtr($string, $table);
	    $string = Mage::getSingleton('catalog/product_url')->formatUrlKey($string);
	    return $string;
	}
}