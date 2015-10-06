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


class Mirasvit_MstCore_Helper_Cron extends Mage_Core_Helper_Data
{
	/**
	 * Ð¤ÑÐ½ÐºÑÐ¸Ñ Ð¿Ð¾Ð·Ð²Ð¾Ð»ÑÐµÑ Ð¿Ð¾ÐºÐ°Ð·ÑÐ²Ð°ÑÑ ÑÐ¾Ð¾Ð±ÑÐµÐ½Ð¸Ðµ Ð¾ Ð½ÐµÑÐ°Ð±Ð¾ÑÐ°ÑÑÐµÐ¼ ÐºÑÐ¾Ð½Ðµ Ð² Ð°Ð´Ð¼Ð¸Ð½ÐºÐµ. ÐÑÐ·ÑÐ²Ð°ÑÑ ÐµÐµ Ð½ÑÐ¶Ð½Ð¾ Ð² Ð½Ð°ÑÐ°Ð»Ðµ Action Ð² ÐºÐ¾Ð½ÑÑÐ¾Ð»Ð»ÐµÑÐµ Ð°Ð´Ð¼Ð¸Ð½ÐºÐ¸.
	 * @param  string  $jobCode ÐºÐ¾Ð´ ÐºÑÐ¾Ð½Ð° ÐºÐ¾ÑÐ¾ÑÑÐ¹ Ð¼Ñ Ð¿ÑÐ¾Ð²ÐµÑÑÐµÐ¼ (Ð¸Ð· config.xml)
	 * @param  boolean $link    ÑÑÑÐ»ÐºÐ° Ð½Ð° Ð´Ð¾ÐºÑÐ¼ÐµÐ½ÑÐ°ÑÐ¸Ñ Ð¿Ð¾ ÐºÑÐ¾Ð½Ñ
	 */
 	public function checkCronStatus($jobCode, $link = false)
    {
    	if (!$this->isCronRunning($jobCode)) {
    		$message = $this->__('Cron is not running. You need to setup a cron job for Magento. To do this, add following expression to your crontab <br><i>%s</i>', $this->getCronExpression());
    		if ($link) {
    			$message .= $this->__('<br><a href="%s" target="_blank">Read more</a>', $link);
    		}
            Mage::getSingleton('adminhtml/session')->addError($message);
    	}
	}

 	public function isCronRunning($jobCode)
    {
        $job = Mage::getModel('cron/schedule')->getCollection()
            ->addFieldToFilter('job_code', $jobCode)
            ->addFieldToFilter('status', 'success')
            ->setOrder('scheduled_at', 'desc')
            ->getFirstItem();

        if (!$job->getId()) {
            return false;
        }

        $jobTimestamp = strtotime($job->getExecutedAt());
        $timestamp    = Mage::getSingleton('core/date')->gmtTimestamp();

        if (abs($timestamp - $jobTimestamp) > 6 * 60 * 60) {
            return false;
        }

        return true;
    }

    public function getCronExpression()
    {
        $phpBin = $this->getPhpBin();
        $root   = Mage::getBaseDir();
        $var    = Mage::getBaseDir('var');

        $line = '* * * * * date >> '.$var.DS.'log'.DS.'cron.log;'
            .$phpBin.' -f '.$root.DS.'cron.php >> '.$var.DS.'log'.DS.'cron.log 2>&1;';

        return $line;
    }

    public function getPhpBin()
    {
        $phpBin = 'php';

        if (PHP_BINDIR) {
            $phpBin = PHP_BINDIR.DS.'php';
        }

        return $phpBin;
    }
}
