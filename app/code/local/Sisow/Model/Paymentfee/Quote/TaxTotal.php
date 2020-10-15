<?php
/**
 * Payment fee tax address quote
 *
 * Class to handle the payment fee tax on a address quote
 *
 */

class Sisow_Model_Paymentfee_Quote_TaxTotal extends Mage_Sales_Model_Quote_Address_Total_Tax
{

    /**
     * Collect the order total
     *
     * @param object $address The address instance to collect from
     *
     * @return Sisow_Model_Quote_TaxTotal
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $quote = $address->getQuote();
        if (($quote->getId() == null)
            || ($address->getAddressType() != "shipping")
        ) {
            return $this;
        }

        $payment = $quote->getPayment();

        if ((substr($payment->getMethod(), 0, 6) != 'sisow_')
            && (!count($quote->getPaymentsCollection())
            || (!$payment->hasMethodInstance()))
        ) {
            return $this;
        }
    
        $methodInstance = $payment->getMethodInstance();

        if (substr($methodInstance->getCode(), 0, 6) != 'sisow_') {
            return $this;
        }

        $helper = Mage::helper('sisow/paymentfee');
        
        $fee = $helper->getPaymentFeeArray(
            $methodInstance->getCode(), 
            $quote,
			$address->getSubtotal()
        );
            
        if (!is_array($fee)) {
            return $this;
        }
    
        $address->setTaxAmount($address->getTaxAmount() + $fee['taxamount']);
        $address->setBaseTaxAmount(
            $address->getBaseTaxAmount() + $fee['base_taxamount']
        );

        $address->setInvoiceTaxAmount($fee['taxamount']);
        $address->setBaseInvoiceTaxAmount($fee['base_taxamount']);
        
        return $this;
    }

}
