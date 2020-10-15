<?php
/**
 * Payment fee totals quote
 *
 * Class to handle the payment fee totals quote
 *
 */

class Sisow_Model_Paymentfee_Quote_Quote extends Mage_Sales_Model_Quote
{
    /**
     * Get all quote totals
     *
     * We need to remove a sisow_tax line that is created from
     * one of our models. Otherwise there will be two tax lines in the
     * checkout
     *
     * @return array
     */
    public function getTotals()
    {
        $totals = parent::getTotals();
        unset($totals['sisow_tax']);
        $totalsIndex = array_keys($totals);
        if (array_search('sisow_fee', $totalsIndex) === false) {
            return $totals;
        }

        unset($totalsIndex[array_search('sisow_fee', $totalsIndex)]);
        $fee = $totals['sisow_fee'];
        unset($totals['sisow_fee']);

        $feeIndex = array_search('shipping', $totalsIndex);
        if ($feeIndex === false) {
            $feeIndex = array_search('subtotal', $totalsIndex)+1;
        }

        $sortedTotals = array();
        $size = count($totalsIndex);
        for ($i=0; $i<$size; $i++) {
            if ($i == $feeIndex) {
                $sortedTotals['sisow_fee'] = $fee;
            }

            $sortedTotals[array_shift($totalsIndex)] = array_shift($totals);
        }

        return $sortedTotals;
    }

}


