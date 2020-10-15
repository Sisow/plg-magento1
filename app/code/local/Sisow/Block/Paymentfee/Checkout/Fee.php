<?php
/**
 * File used to display a payment fee on the order total
 *
 * Class used to create a payment fee block
 *
 */

class Sisow_Block_Paymentfee_Checkout_Fee extends Mage_Checkout_Block_Total_Default
{
    protected $_template = 'sisow/paymentfee/checkout/fee.phtml';

    /**
     * Get Payment fee including tax
     *
     * @return float
     */
    public function getInvoiceFeeIncludeTax()
    {
        return $this->getTotal()->getAddress()->getInvoiceFee();
    }

    /**
     * Get Payment fee excluding tax
     *
     * @return float
     */
    public function getInvoiceFeeExcludeTax()
    {
        return $this->getTotal()->getAddress()->getInvoiceFeeExcludedVat();
    }

    /**
     * Checks if both including and excluding tax prices should be shown
     *
     * @return bool
     */
    public function displayBoth()
    {
        return Mage::helper("tax")->displayCartBothPrices();
    }

    /**
     * Checks if only including tax price should be shown
     *
     * @return bool
     */
    public function displayIncludeTax()
    {
        return Mage::helper("tax")->displayCartPriceInclTax();
    }

    /**
     * Get the label to display for excluding tax
     *
     * @return string
     */
    public function getExcludeTaxLabel()
    {
        return Mage::helper("tax")->getIncExcTaxLabel(false);
    }

    /**
     * Get the label to display for including tax
     *
     * @return string
     */
    public function getIncludeTaxLabel()
    {
        return Mage::helper("tax")->getIncExcTaxLabel(true);
    }

}
