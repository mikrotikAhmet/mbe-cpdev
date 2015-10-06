<?php
/**
 *
 * This block does not use the rendererers, only it's specified tempate
 * handles display, the supplied template would need to be modified to fit the design.
 *
 */

class Vbw_Punchout_Block_Checkout_Cart_Totals
    extends Mage_Checkout_Block_Cart_Totals
{

    /**
     * Render totals html for specific totals area (footer, body)
     *
     * @param   string|array $code
     * @param   int $colspan
     * @return  string
     */
    public function renderTotalsByCode($codes = array(), $colspan = 1)
    {
        $codes = (array) $codes;
        $totalsByCode = array();
        foreach($this->getTotals() as $total) {
            if (in_array($total->getCode(),$codes)) {
                $totalsByCode[$total->getCode()] = $this->renderTotal($total, null, $colspan);
            }
        }
        $html = '';
        foreach ($codes AS $code) {
            $html .= $totalsByCode[$code];
        }
        return $html;
    }

    public function getTotalByCode ($code)
    {
        $return = null;
        foreach($this->getTotals() as $total) {
            if ($total->getCode() == $code) {
                $return = $total;
                // let it finish the iteration
            }
        }
        return $return;
    }

}
