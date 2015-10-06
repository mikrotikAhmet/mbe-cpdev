<?php
class Cpdev_Eav_Model_Entity_Increment_Numeric extends Mage_Eav_Model_Entity_Increment_Abstract
{
    public function getNextId()
    {
        $last = $this->getLastId();

        if (strpos($last, $this->getPrefix()) === 0) {
            $last = (int)substr($last, strlen($this->getPrefix()));
        } else {
            $last = (int)$last;
        }

        //$next = $last+1;

        $write  = Mage::getSingleton('core/resource')->getConnection('core_write');
        $result = $write->query("SELECT temp.rnd 
            FROM sales_flat_order c,(SELECT FLOOR((RAND()*99999999)) AS rnd FROM sales_flat_order LIMIT 0,50) AS temp
            WHERE c.increment_id NOT IN (temp.rnd) LIMIT 1");
        $random = $result->fetch();

        $next = $random['rnd'];

        return $this->format($next);
    }
}
