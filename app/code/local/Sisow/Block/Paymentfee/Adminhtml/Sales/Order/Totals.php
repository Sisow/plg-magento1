<?php
/**
 * File used to include the invoice fee to the order totals on the admin page
 *
 * Order totals handling class.
 *
 */
 
class Sisow_Block_Paymentfee_Adminhtml_Sales_Order_Totals extends Mage_Adminhtml_Block_Sales_Order_Totals
{

    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
        parent::_initTotals();

        $order = $this->getOrder();
        $payment = $order->getPayment();

        if (substr($payment->getMethod(), 0, 5) != "sisow") {
            return $this;
        }
        
        return Mage::helper('sisow/paymentfee')->addToBlock($this);
    }

}
