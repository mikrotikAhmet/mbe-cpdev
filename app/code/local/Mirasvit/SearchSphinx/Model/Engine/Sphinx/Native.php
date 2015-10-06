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


/**
 * ÐÐ»Ð°ÑÑ ÑÐµÐ°Ð»Ð¸Ð·ÑÐµÑ Ð¼ÐµÑÐ¾Ð´Ñ Ð´Ð»Ñ ÑÐ°Ð±Ð¾ÑÑ ÑÐ¾ ÑÑÐ¸Ð½ÐºÑÐ¾Ð¼ Ð½Ð° ÑÐ¾Ð¼Ð¶Ðµ ÑÐµÑÐ²ÐµÑÐµ ÑÑÐ¾ Ð¸ magento
 * ÐÐ¾Ð¿Ð¾Ð»Ð½ÑÐµÑ Ð±Ð°Ð·Ð¾Ð²ÑÐ¹ ÐºÐ»Ð°ÑÑ Mirasvit_SearchSphinx_Model_Engine_Sphinx Ð¼ÐµÑÐ¾Ð´Ð°Ð¼Ð¸ ÑÐ¿ÑÐ°Ð²Ð»ÐµÐ½Ð¸Ñ Ð¸Ð½Ð´ÐµÐºÑÐ¾Ð¼ Ð¸ Ð´ÐµÐ¼Ð¾Ð½Ð¾Ð¼
 * reindex/delta reindex/stop/start
 *
 * @category Mirasvit
 * @package  Mirasvit_SearchSphinx
 */
class Mirasvit_SearchSphinx_Model_Engine_Sphinx_Native extends Mirasvit_SearchSphinx_Model_Engine_Sphinx
{
    const SEARCHD                 = 'searchd';
    const INDEXER                 = 'indexer';
    const REINDEX_SUCCESS_MESSAGE = 'rotating indices: succesfully sent SIGHUP to searchd';

    protected $_indexerCommand    = null;
    protected $_searchdCommand    = null;

    /**
     * Ð£ÑÑÐ°Ð½Ð°Ð²Ð»Ð¸Ð²Ð°ÐµÐ¼ Ð¿ÐµÑÐµÐ¼ÐµÐ½Ð½ÑÐ¹ ÑÐ²Ð¾Ð¹ÑÑÐ²ÐµÐ½Ð½ÑÐµ Ð´Ð»Ñ Ð»Ð¾ÐºÐ°Ð»ÑÐ½Ð¾ ÑÑÐ¸Ð½ÐºÑÐ°
     */
    public function __construct()
    {
        parent::__construct();

        $binPath = Mage::getStoreConfig('searchsphinx/general/bin_path');
        // ÐµÑÐ»Ð¸ Ð² Ð¿ÑÑÐ¸ ÐµÑÑÑ searchd, ÑÐ±ÐµÑÐ°ÐµÐ¼ ÐµÐ³Ð¾
        if (substr($binPath, strlen($binPath) - strlen(self::SEARCHD)) == self::SEARCHD) {
            $binPath = substr($binPath, 0, strlen($binPath) - strlen(self::SEARCHD));
        }

        $this->_indexerCommand = $binPath.self::INDEXER;
        $this->_searchdCommand = $binPath.self::SEARCHD;

        $this->_spxHost        = Mage::getStoreConfig('searchsphinx/general/host');
        $this->_spxPort        = Mage::getStoreConfig('searchsphinx/general/port');

        $this->_spxHost        = $this->_spxHost ? $this->_spxHost : 'localhost';
        $this->_spxPort        = intval($this->_spxPort ? $this->_spxPort : '9315');

        $this->_basePath       = Mage::getBaseDir('var').DS.'sphinx';

        return $this;
    }

    /**
     * ÐÐµÑÐµÐ¸Ð½Ð´ÐµÐºÑÐ°ÑÐ¸Ñ - Ð¾ÑÐ¿ÑÐ°Ð²ÐºÐ° http Ð·Ð°Ð¿ÑÐ¾ÑÐ°
     *
     * @param  boolean $delta Ð²ÑÐ¿Ð¾Ð»Ð½Ð¸ÑÑ Ð´ÐµÐ»ÑÐ°-ÑÐµÐ¸Ð½Ð´ÐµÐºÑ
     *
     * @return string
     */
    public function reindex($delta = false)
    {
        return $this->_request('reindex');
    }

    /**
     * ÐÐ°Ð¿ÑÑÐº Ð´ÐµÐ¼Ð¾Ð½Ð° - Ð¾ÑÐ¿ÑÐ°Ð²ÐºÐ° http Ð·Ð°Ð¿ÑÐ¾ÑÐ°
     */
    public function start()
    {
        $error = $this->_request('start');

        if ($error) {
            Mage::throwException($error);
        }

        return $this;
    }

    /**
     * ÐÑÑÐ°Ð½Ð¾Ð²ÐºÐ° Ð´ÐµÐ¼Ð¾Ð½Ð° - Ð¾ÑÐ¿ÑÐ°Ð²ÐºÐ° http Ð·Ð°Ð¿ÑÐ¾ÑÐ°
     */
    public function stop()
    {
        $error = $this->_request('stop');

        if ($error) {
            Mage::throwException($error);
        }

        return $this;
    }

    /**
     * Ð ÐµÑÑÐ°ÑÑ Ð´ÐµÐ¼Ð¾Ð½Ð° - Ð¾ÑÐ¿ÑÐ°Ð²ÐºÐ° 2Ñ http Ð·Ð°Ð¿ÑÐ¾ÑÐ¾Ð² (ÑÑÐ¾Ð¿, ÑÑÐ°ÑÑ)
     */
    public function restart()
    {
        $this->stop();
        $this->start();

        return $this;
    }

    /**
     * ÐÑÐ¿Ð¾Ð»Ð½ÑÐµÑ ÑÐµÐ¸Ð½Ð´ÐµÐºÑ Ð²ÑÐµÑ Ð°ÐºÑÐ¸Ð²Ð½ÑÑ Ð¸Ð½Ð´ÐµÐºÑÐ¾Ð²
     *
     * @param  boolean $delta Ð²ÑÐ¿Ð¾Ð»Ð½Ð¸ÑÑ Ð´ÐµÐ»ÑÐ°-ÑÐµÐ¸Ð½Ð´ÐµÐºÑ
     *
     * @return string
     */
    public function doReindex($delta = false)
    {
        $this->makeConfigFile();

        if (!$this->isSphinxFounded()) {
            Mage::throwException($this->_indexerCommand.': command not found');
        }

        if (!$this->isIndexerRunning()) {
            $indexes = Mage::helper('searchindex/index')->getIndexes();
            $toReindex = array();
            foreach ($indexes as $index) {
                $indexCode = $index->getCode();
                if ($delta) {
                    $indexCode = 'delta_'.$indexCode;
                }
                $toReindex[] = $indexCode;
            }

            $exec   = $this->_exec($this->_indexerCommand.' --config '.$this ->_configFilepath.' --rotate '.implode(' ', $toReindex));
            $result = ($exec['status'] == 0) || (strpos($exec['data'], self::REINDEX_SUCCESS_MESSAGE) !== FALSE);

            if (!$result) {
                Mage::throwException('Error on reindex '.$exec['data']);
            }

            if ($delta) {
                $this->mergeDeltaWithMain();
            }
            $this->restart();
        } else {
            Mage::throwException('Reindex already run, please wait... '.$this->isIndexerRunning());
        }

        return 'Index has been successfully rebuilt';
    }

    /**
     * ÐÑÐ¿Ð¾Ð»Ð½ÑÐµÑ Ð·Ð°Ð¿ÑÑÐº Ð´ÐµÐ¼Ð¾Ð½Ð°
     */
    public function doStart()
    {
        $this->stop();

        if (!$this->isSphinxFounded()) {
            Mage::throwException($this->_searchdCommand.': command not found');
        }

        if (!is_readable($this->_configFilepath)) {
            Mage::throwException("File ".$this->_configFilepath." don't readable");
        }

        $command = $this->_searchdCommand.' --config '.$this->_configFilepath;
        $exec = $this->_exec($command);
        if ($exec['status'] !== 0) {
            Mage::throwException('Error when running searchd '.$exec['data']);
        }

        return $this;
    }

    /**
     * ÐÑÐ¿Ð¾Ð»Ð½ÑÐµÑ Ð¾ÑÑÐ°Ð½Ð¾Ð²ÐºÑ Ð´ÐµÐ¼Ð¾Ð½Ð°
     */
    public function doStop()
    {
        $find    = 'ps aux | grep searchd | grep '.$this->_configFilepath.'  | awk \'{print $2}\'';
        $exec = $this->_exec($find);

        foreach(explode(PHP_EOL, $exec['data']) as $id) {
            $command = 'kill -9 '.$id;
            $this->_exec($command);
        }

        return $this;
    }

    /**
     * ÐÑÐ¾Ð²ÐµÑÑÐµÑ Ð·Ð°Ð¿ÑÑÐµÐ½-Ð»Ð¸ ÑÐµÐ¸Ð½Ð´ÐµÐºÑ
     *
     * @return boolean
     */
    public function isIndexerRunning()
    {
        $status = false;

        $command = 'ps aux | grep '.self::INDEXER.' | grep '.$this->_configFilepath;
        $exec = $this->_exec($command);
        if ($exec['status'] === 0) {
            $pos = strpos($exec['data'], '--rotate');
            if ($pos !== false) {
                $status = $exec['data'];
                return $status;
            }
        }

        return $status;
    }

    /**
     * ÐÑÐ¾Ð²ÐµÑÑÐµÑ Ð·Ð°Ð¿ÑÑÐµÐ½-Ð»Ð¸ Ð´ÐµÐ¼Ð¾Ð½
     *
     * @return boolean
     */
    public function isSearchdRunning()
    {
        $command = 'ps aux | grep '.self::SEARCHD.' | grep '.$this->_configFilepath;
        $exec = $this->_exec($command);

        if ($exec['status'] === 0) {
            $pos = strpos($exec['data'], self::SEARCHD.' --config');

            if ($pos !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * ÐÑÐ¾Ð²ÐµÑÑÑ Ð½Ð°Ð¹Ð´ÐµÐ½-Ð»Ð¸ Ð½Ð° ÑÐµÑÐ²ÐµÑ ÑÑÐ¸Ð½ÐºÑ (searchd)
     *
     * @return boolean
     */
    public function isSphinxFounded()
    {
        $exec = $this->_exec($this->_searchdCommand.' --config /fake/fake/sphinx.conf');

        if (strpos($exec['data'], 'sphinx.conf') === false) {
            return false;
        }

        return true;
    }

    /**
     * ÐÐ°Ð¿ÑÑÐºÐ°ÐµÑ Ð¾Ð±ÑÐµÐ´ÐµÐ½ÐµÐ½Ð¸Ðµ Ð¾ÑÐ½Ð¾Ð²Ð½Ð¾Ð³Ð¾ Ð¸Ð½Ð´ÐµÐºÑÐ° Ñ Ð´ÐµÐ»ÑÐ°-Ð¸Ð½Ð´ÐµÐºÑÐ¾Ð¼
     * Ð´Ð»Ñ Ð²ÑÐµÑ Ð°ÐºÑÐ¸Ð²Ð½ÑÑ Ð¸Ð½Ð´ÐµÐºÑÐ¾Ð²
     */
    public function mergeDeltaWithMain()
    {
        $indexes = Mage::helper('searchindex/index')->getIndexes();
        foreach ($indexes as $index) {
            $exec = $this->_exec($this->_indexerCommand.' --config '.$this ->_configFilepath.' --merge '.$index->getCode().' delta_'.$index->getCode().' --merge-dst-range deleted 0 0 --rotate');
        }

        return $this;
    }

    /**
     * ÐÑÐ¿Ð¾Ð»Ð½ÐµÐ½Ð¸Ðµ php ÐºÐ¾Ð¼Ð¼Ð°Ð½Ð´Ñ exec() Ñ Ð¿ÑÐ¾Ð²ÐµÑÐºÐ¾Ð¹ Ð½Ð° ÑÑÑÐµÑÑÐ²Ð¾Ð²Ð°Ð½Ð¸Ðµ ÑÑÐ½ÐºÑÐ¸Ð¸
     * ÐÐ° Ð½ÐµÐºÐ¾ÑÐ¾ÑÑÑ ÑÐµÑÐ²ÐµÑÐ°Ñ ÑÑÐ½ÐºÑÐ¸Ñ Ð½Ð°ÑÐ¾Ð´Ð¸ÑÑÑÑ Ð² Ð¸Ð³Ð½Ð¾Ñ-Ð»Ð¸ÑÑÐµ. Ð ÑÑÐ¾Ð¼ ÑÐ»ÑÑÐ°Ðµ ÐµÐµ Ð½Ð°Ð´Ð¾ Ð²ÐºÐ»ÑÑÐ¸ÑÑ ÑÐµÑÐµÐ· php.ini
     *
     * @param  string $command ÐºÐ¾Ð¼Ð¼Ð°Ð½Ð´ÑÐ°
     *
     * @return array
     */
    protected function _exec($command)
    {
        $status = null;
        $data   = array();

        if (function_exists('exec')) {
            exec($command, $data, $status);
            Mage::helper('mstcore/logger')->log($this, __FUNCTION__, $command."\n".implode("\n", $data));
        } else {
            Mage::helper('mstcore/logger')->log($this, __FUNCTION__, 'PHP function "exec" not available');

            Mage::throwException('PHP function "exec" not available');
        }

        return array('status' => $status, 'data' => implode(PHP_EOL, $data));
    }

    /**
     * ÐÑÐ¿ÑÐ°Ð²Ð»ÑÐµÑ http Ð·Ð°Ð¿ÑÐ¾Ñ Ð½Ð° ÐºÐ¾Ð½ÑÑÐ¾Ð»ÐµÐµÑ ÑÐ°ÑÑÐ¸ÑÐµÐ½Ð¸Ñ
     * Ð¢Ð°ÐºÐ¸Ð¼ Ð¾Ð±ÑÐ°Ð·Ð¾Ð¼ Ð²ÑÐµ Ð´ÐµÐ¹ÑÑÐ²Ð¸Ñ ÑÐ¾ ÑÑÐ¸Ð½ÐºÑÐ¾Ð¼ Ð²ÑÐ¿Ð¾Ð»Ð½ÑÑÑÑÑÑ Ð¾Ñ apache Ð¿Ð¾Ð»ÑÐ·Ð¾Ð²Ð°ÑÐµÐ»Ñ
     *
     * @param  string $command ÐºÐ¾Ð¼Ð¼Ð°Ð½Ð´Ð° (ÑÑÐ°ÑÑ\ÑÑÐ¾Ð¿\ÑÐµÐ¸Ð½Ð´ÐµÐºÑ)
     * @return string
     */
    protected function _request($command)
    {
        $httpClient = new Zend_Http_Client();
        $httpClient->setConfig(array('timeout' => 60000));

        Mage::register('custom_entry_point', true, true);

        $store  = Mage::app()->getStore(0);
        $url    = $store->getUrl('searchsphinx/action/'.$command);
        $result = $httpClient->setUri($url)->request()->getBody();

        Mage::helper('mstcore/logger')->log($this, __FUNCTION__, $url."\n".$result);

        return $result;
    }

}