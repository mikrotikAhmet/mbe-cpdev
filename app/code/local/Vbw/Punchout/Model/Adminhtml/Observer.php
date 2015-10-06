<?php

class Vbw_Punchout_Model_Adminhtml_Observer
{

    public function checkUseOfStashInMenu ()
    {
        if (Mage::getStoreConfig('vbw_punchout/api/enable_stash_admin')) {
            $parent = Mage::getSingleton('admin/config')->getAdminhtmlConfig()->getNode('menu');
            $punchout = $parent->sales->children->addChild('Punchout');
            $punchout->addAttribute('translate','title');
            $punchout->addChild('title','Punchout Stash');
            $punchout->addChild('sort_order','9999');
            $punchout->addChild('action','punchout/adminhtml_stash');

            /*
<menu>
<sales>
   <children>
       <Punchout translate="title">
           <title>Punchout Stash</title>
           <sort_order>30</sort_order>
           <action>punchout/adminhtml_stash</action>
       </Punchout>
   </children>
</sales>
</menu>                 */
        }

    }
}
