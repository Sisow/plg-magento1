<?php
/**
 * File used to display a invoice fee on a invoice
 *
 * Class used to add the invoice fee to a invoice
 *
 */

class Sisow_Block_Paymentfee_Adminhtml_Sales_Invoice_Totals_Fee extends Mage_Adminhtml_Block_Sales_Order_Invoice_Totals
{

    /**
     * Initialize order totals array
     *
     * @return Mage_Adminhtml_Block_Sales_Order_Invoice_Totals
     */
    public function _initTotals()
    {
        parent::_initTotals();

        $payment = $this->getOrder()->getPayment();
        if (substr($payment->getMethod(), 0, 5) != "sisow") {
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
