<?php


require_once "Mage/Sales/controllers/DownloadController.php";

class Vbw_Punchout_MController
    extends Mage_Sales_DownloadController
{

    public function dAction()
    {

        $quoteItemOptionId = $this->getRequest()->getParam('i');
        $fileName = $this->getRequest()->getParam('f');

        /** @var $option Mage_Sales_Model_Quote_Item_Option */
        $option = Mage::getModel('sales/quote_item_option')->load($quoteItemOptionId);

        if (!$option->getId()
                || empty($fileName)) {
            $this->_forward('noRoute');
            return;
        }

        /** @var $salesHelper Vbw_Punchout_Helper_Sales */
        $salesHelper = Mage::helper('vbw_punchout/sales');
        $type = $salesHelper->getOptionType($option);

        if ($type != 'file') {
            $this->_forward('noRoute');
            return;
        }

        try {
            $info = unserialize($option->getValue());
            if (empty($info['title'])
                   || $info['title'] != $fileName) {
                throw new Exception('no match');
            }
            $this->_downloadFileAction($info);
        } catch (Exception $e) {
            $this->_forward('noRoute');
        }
        exit(0);
    }

    /**
     * Custom options downloader
     *
     * @param mixed $info
     */
    protected function _downloadFileAction($info)
    {
        try {
            $filePath = Mage::getBaseDir() . $info['order_path'];
            if ((!is_file($filePath) || !is_readable($filePath)) && !$this->_processDatabaseFile($filePath)) {
                //try get file from quote
                $filePath = Mage::getBaseDir() . $info['quote_path'];
                if ((!is_file($filePath) || !is_readable($filePath)) && !$this->_processDatabaseFile($filePath)) {
                    throw new Exception();
                }
            }
            $this->_prepareDownloadResponse($info['title'], array(
                'value' => $filePath,
                'type'  => 'filename'
            ));
        } catch (Exception $e) {
            $this->_forward('noRoute');
        }
    }

}