<?php

class Sisow_Model_Total_Address_QuoteTax
    extends Sisow_Model_Total_Address_Abstract
{
    /**
     * The code of this 'total'.
     *
     * @var string
     */
    protected $_totalCode = 'sisow_fee_tax';
    /**
     * Collect the Sisow Payment fee tax for the given address.
     *
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return $this
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        /**
         * We can only add the fee to the shipping address.
         */
        if ($address->getAddressType() !='shipping') {
            return $this;
        }
        $quote = $address->getQuote();
        $store = $quote->getStore();
        if (!$quote->getId()) {
            return $this;
        }
        /**
         * First, reset the fee amounts to 0 for this address and the quote.
         */
        $address->setSisowFeeTax(0)
                ->setBaseSisowFeeTax(0);
        $quote->setSisowFeeTax(0)
              ->setBaseSisowFeeTax(0);
        $items = $address->getAllItems();
        if (!count($items)) {
            return $this;
        }
        if (!$address->getSisowFee() || !$address->getBaseSisowFee()) {
            return $this;
        }

        $baseFee = $address->getBaseSisowFee();
        $fee     = $store->convertPrice($baseFee);

        $address->setSisowFeeInclTax($fee)
                ->setBaseSisowFeeInclTax($baseFee);

        $items = $address->getAllItems();
        if (!count($items)) {
            return $this;
        }
        /**
         * Get the tax request and corresponding tax rate.
         */
        $taxRequest = $this->_getSisowFeeTaxRequest($quote);
        if (!$taxRequest) {
            return $this;
        }
        $taxRate = $this->_getSisowFeeTaxRate($taxRequest);
        if (!$taxRate || $taxRate <= 0) {
            return $this;
        }
        /**
         * Calculate the tax for the fee using the tax rate.
         */
        $paymentMethod = $quote->getPayment()->getMethod();
        $baseFee = $address->getBaseSisowFee();
        $fee     = $store->convertPrice($baseFee);
        $feeTax     = $this->_getSisowFeeTax($address, $taxRate, $fee, false);
        $baseFeeTax = $this->_getBaseSisowFeeTax($address, $taxRate, $baseFee, false);
        /**
         * Get all taxes that were applied for this tax request.
         */
        $appliedRates = Mage::getSingleton('tax/calculation')
                            ->getAppliedRates($taxRequest);
        /**
         * Save the newly applied taxes.
         */
        $this->_saveAppliedTaxes(
            $address,
            $appliedRates,
            $feeTax,
            $baseFeeTax,
            $taxRate
        );
        /**
         * Update the total amounts.
         */
        $address->setTaxAmount($address->getTaxAmount() + $feeTax)
                ->setBaseTaxAmount($address->getBaseTaxAmount() + $baseFeeTax)
                ->setSisowFeeTax($feeTax)
                ->setBaseSisowFeeTax($baseFeeTax)
                ->setSisowFeeInclTax($fee+$feeTax)
                ->setBaseSisowFeeInclTax($baseFee+$baseFeeTax);
        $address->addTotalAmount('nominal_tax', $feeTax);
        $address->addBaseTotalAmount('nominal_tax', $baseFeeTax);
        $quote->setSisowFeeTax($feeTax)
              ->setBaseSisowFeeTax($baseFeeTax);
        return $this;
    }
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        return $this;
    }
}