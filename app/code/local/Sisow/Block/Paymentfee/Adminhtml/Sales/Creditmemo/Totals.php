<?php
/**
 * File used to display a invoice fee on a credit memo
 *
 * Class used to add the invoice fee to a credit memo
 *
 */

class Sisow_Block_Paymentfee_Adminhtml_Sales_Creditmemo_Totals extends Mage_Sales_Block_Order_Creditmemo_Totals
{

    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Creditmemo_Totals
     */
    public function _initTotals()
    {
        parent::_initTotals();

        $payment = $this->getOrder()->getPayment();
        if (substr($payment->getMethod(), 0, 6) != "sisow_") {
            return $this;
        }

/*
        $info = $payment->getMethodInstance()->getInfoInstance();
        if (!$info->getAdditionalInformation("invoice_fee")) {
            return $this;
        }
*/

       return Mage::helper('sisow/paymentfee')->addToBlock($this);
    }

}
