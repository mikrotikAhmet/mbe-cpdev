<?php

class Vbw_Punchout_Block_Returnlink
    extends Mage_Page_Block_Template_Links
{
    public function addReturnLink(){
        $top_links = $this->getParentBlock();
        /** @var $helper Vbw_Punchout_Helper_Config */
        $helper = Mage::helper('vbw_punchout/config');
        if (Mage::getSingleton('vbw_punchout/session')->isPunchoutSession()
                && $helper->getConfig('display/show_return') == 1) {
            $url = $helper->getConfig('display/return_url');
            $label = $helper->getConfig('display/return_label');
            if (empty($url)){
                $session = Mage::GetSingleton("vbw_punchout/session");
                $url = $session->getRemoteHost() ."/gateway/link/return/id/". $session->getPunchoutId() ."/?redirect=1";
                //$url = Mage::getSingleton('vbw_punchout/session')->getPunchoutRequest()->getBody()->getPostForm();
            }
            $target = $helper->getConfig('display/return_target');
            $use = $helper->useReturnLink();
            if (Mage::getSingleton('vbw_punchout/session')->isPunchoutSession()){
                if ($use == 3){
                    return $this;
                } else if ($use == 1){
                    if (method_exists($top_links,'addLink')) {
                        $top_links->addLink(
                            $label, //Name
                            $url,
                            $label, //Title
                            0,      //Prepare (non internal links)
                            array(),
                            999,    //Position
                            null,
                            'target="' . $target . '" ' //aParams
                        );
                    }
                }
            }
        }
    }

}