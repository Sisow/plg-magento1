<?php

abstract class Sisow_Model_Total_Address_Abstract extends Mage_Tax_Model_Sales_Total_Quote_Tax
{
    /**
     * @var string
     */
    protected $_totalCode;
    /**
     * @var boolean
     */
    protected $_feeIsInclTax = null;
    /**
     * Constructor method.
     *
     * Sets several class variables.
     */
    public function __construct()
    {
        $this->setCode($this->_totalCode);
        $this->setTaxCalculation(Mage::getSingleton('tax/calculation'));
        $this->_helper = Mage::helper('tax');
        $this->_config = Mage::getSingleton('tax/config');
        //$this->_weeeHelper = Mage::helper('weee');
    }
    /**
     * @return Mage_Tax_Model_Calculation
     */
    public function getTaxCalculation()
    {
        $taxCalculation = $this->_calculator;
        if ($taxCalculation) {
            return $taxCalculation;
        }
        $taxCalculation = Mage::getSingleton('tax/calculation');
        $this->setTaxCalculation($taxCalculation);
        return $taxCalculation;
    }
    /**
     * @param Mage_Tax_Model_Calculation $taxCalculation
     *
     * @return $this
     */
    public function setTaxCalculation(Mage_Tax_Model_Calculation $taxCalculation)
    {
        $this->_calculator = $taxCalculation;
        return $this;
    }
    /**
     * Get whether the Sisow fee is incl. tax.
     *
     * @param int|Mage_Core_Model_Store|null $store
     *
     * @return bool
     */
    public function getFeeIsInclTax($store = null)
    {
        if (null !== $this->_feeIsInclTax) {
            return $this->_feeIsInclTax;
        }
        
        if (is_null($store)) {
            $storeId = Mage::app()->getStore()->getId();
        } elseif ($store instanceof Mage_Core_Model_Store) {
            $storeId = $store->getId();
        } else {
            $storeId = $store;
        }
        $feeIsInclTax = true;//Mage::getStoreConfigFlag(self::XPATH_BUCKAROO_FEE_INCLUDING_TAX, $storeId);
        
        $this->_feeIsInclTax = $feeIsInclTax;
        return $feeIsInclTax;
    }
    /**
     * Get the tax request object for the current quote.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool|Varien_Object
     */
    protected function _getSisowFeeTaxRequest(Mage_Sales_Model_Quote $quote)
    {
        $store = $quote->getStore();
        $sisowTaxClass      = Mage::getStoreConfig('payment/' . $quote->getPayment()->getMethodInstance()->getCode() . '/payment_fee_tax', $store);
        /**
         * If no tax class is configured for the Sisow fee, there is no tax to be calculated.
         */
        if (!$sisowTaxClass) {
            return false;
        }
        $taxCalculation   = $this->getTaxCalculation();
        $customerTaxClass = $quote->getCustomerTaxClassId();
        $shippingAddress  = $quote->getShippingAddress();
        $billingAddress   = $quote->getBillingAddress();
        $request = $taxCalculation->getRateRequest(
            $shippingAddress,
            $billingAddress,
            $customerTaxClass,
            $store
        );
        $request->setProductClassId($sisowTaxClass);
        return $request;
    }
    /**
     * Get the tax rate based on the previously created tax request.
     *
     * @param Varien_Object $request
     *
     * @return float
     */
    protected function _getSisowFeeTaxRate($request)
    {
        $rate = $this->getTaxCalculation()->getRate($request);
        return $rate;
    }
    /**
     * Get the fee tax based on the shipping address and tax rate.
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @param float                          $taxRate
     * @param float|null                     $fee
     * @param boolean                        $isInclTax
     *
     * @return float
     */
    protected function _getSisowFeeTax($address, $taxRate, $fee = null, $isInclTax = false)
    {
        if (is_null($fee)) {
            $fee = (float) $address->getSisowFee();
        }
        $taxCalculation = $this->getTaxCalculation();
        $feeTax = $taxCalculation->calcTaxAmount(
            $fee,
            $taxRate,
            $isInclTax,
            false
        );
        return $feeTax;
    }
    /**
     * Get the base fee tax based on the shipping address and tax rate.
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @param float                          $taxRate
     * @param float|null                     $fee
     * @param boolean                        $isInclTax
     *
     * @return float
     */
    protected function _getBaseSisowFeeTax($address, $taxRate, $fee = null, $isInclTax = false)
    {
        if (is_null($fee)) {
            $fee = (float) $address->getBaseSisowFee();
        }
        $taxCalculation = $this->getTaxCalculation();
        $baseFeeTax = $taxCalculation->calcTaxAmount(
            $fee,
            $taxRate,
            $isInclTax,
            false
        );
        return $baseFeeTax;
    }
    /**
     * Process model configuration array.
     * This method can be used for changing totals collect sort order
     *
     * @param   array $config
     * @param   store $store
     * @return  array
     */
    public function processConfigArray($config, $store)
    {
        return $config;
    }
}