<?php
/**
 * Payment fee address quote
 *
 * Class to handle the payment fee  on a address quote
 *
 */

class Sisow_Model_Paymentfee_Quote_Total extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    protected $address;

    protected $paymentMethod;

    /**
     * Collect the order total
     *
     * @param object $address The address instance to collect from
     *
     * @return Sisow_Model_Quote_Total
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        if ($address->getAddressType() != "shipping") {
            return $this;
        }
        
        $this->address = $address;
        $this->quote = $address->getQuote();
        $this->payment = $this->quote->getPayment();
        
                if ((substr($this->payment->getMethod(), 0, 6) != 'sisow_'))
        {
            return $this;
                }
        
        $this->_resetValues();

        if ($this->address->getQuote()->getId() == null) {
            return $this;
        }

        $items = $this->address->getAllItems();
        if (!count($items)) {
            return $this;
        }

        
        if ($this->payment->hasMethodInstance()) {
            $this->paymentMethod = $this->payment->getMethodInstance();
            if (substr($this->paymentMethod->getCode(), 0, 6) == 'sisow_') {
                $this->_initInvoiceFee();
            }
        }
    }

    /**
     * Reset the invoice fee variables
     *
     * @return void
     */
    private function _resetValues()
    {
        $this->address->setInvoiceFee(0);
        $this->address->setBaseInvoiceFee(0);
        $this->address->setInvoiceFeeExcludedVat(0);
        $this->address->setBaseInvoiceFeeExcludedVat(0);
        $this->address->setInvoiceTaxAmount(0);
        $this->address->setBaseInvoiceTaxAmount(0);
        $this->address->setInvoiceFeeRate(0);
    }

    /**
     * Initialize the invoice fee variables on the address instance
     *
     * @return void
     */
    private function _initInvoiceFee()
    {
        $helper = Mage::helper('sisow/paymentfee');
        $fee = $helper->getPaymentFeeArray(
            $this->payment->getMethodInstance()->getCode(), 
            $this->quote,
			$this->address->getSubtotal()
        );
        
        $this->address->setBaseInvoiceFee($fee['base_incl']);
        $this->address->setInvoiceFee($fee['incl']);
        $this->address->setBaseInvoiceFeeExcludedVat($fee['base_excl']);
        $this->address->setInvoiceFeeExcludedVat($fee['excl']);
        $this->address->setBaseInvoiceTaxAmount($fee['base_taxamount']);
        $this->address->setInvoiceTaxAmount($fee['taxamount']);
        $this->address->setInvoiceFeeRate($fee['rate']);

        // Add our invoice fee to the address totals
        $this->address->setBaseGrandTotal(
            $this->address->getBaseGrandTotal() + $fee['base_incl']
        );
        $this->address->setGrandTotal(
            $this->address->getGrandTotal() + $fee['incl']
        );
    }

    /**
     * Add invoice fee total information to address
     *
     * @param object $address The address instance
     *
     * @return Sisow_Model_Quote_Total
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        if ($address->getAddressType() != "shipping") {
            return $this;
        }

        $excl = $address->getInvoiceFeeExcludedVat();
        $incl = $address->getInvoiceFee();
        //$lang = Mage::helper('klarnaPaymentModule/lang');
        $country = $address->getCountry();
        $storeId = Mage::app()->getStore()->getId();

        $isOSCEnabled = Mage::getStoreConfig('onestepcheckout/general/rewrite_checkout_links', $storeId);
        if ($isOSCEnabled) {
            $OSCDisplayAmountsInclTax = Mage::getStoreConfig('onestepcheckout/general/display_tax_included', $storeId);
            $value = ($OSCDisplayAmountsInclTax ? $incl : $excl);
        } else {
            $value = $incl;
        }

        if ($value != 0) {
            $address->addTotal(
                array(
                    'code' => $this->getCode(),
                    'title' => Mage::getStoreConfig('payment/'.$this->payment->getMethodInstance()->getCode().'/payment_fee_label'),
                    'value' => $value
                )
            );
        }

        return $this;
    }

}
