<?php
/**
 * Payment fee Credit memo
 *
 * Class to handle the payment fee on a Credit memo
 *
 */

class Sisow_Model_Paymentfee_Creditmemo_Total extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{

    /**
     * Collect the order total
     *
     * @param object $creditmemo The Creditmemo instance to collect from
     *
     * @return Mage_Sales_Model_Order_Creditmemo_Total_Abstract
     */
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $method = $order->getPayment()->getMethodInstance();

        if (substr($method->getCode(), 0, 5) != 'sisow') {
            return $this;
        }

        //$info = $method->getInfoInstance();
        $invoiceFee = $order->getSisowFeeInclTax()  != null ? $order->getSisowFeeInclTax() : $order->getSisowFee()+$order->getSisowFeeTax();
        $baseInvoiceFee = $order->getBaseSisowFeeInclTax()  != null ? $order->getBaseSisowFeeInclTax() : $order->getBaseSisowFee()+$order->getBaseSisowFeeTax();
        $invoiceFeeExludingVat = $order->getSisowFee();
        $baseInvoiceFeeExludingVat =  $order->getBaseSisowFee();
        $invoiceFeeTax = $invoiceFee - $invoiceFeeExludingVat;
        $baseInvoiceFeeTax = $baseInvoiceFee - $baseInvoiceFeeExludingVat;

        if (!$invoiceFee) {
            return $this;
        }

        $creditmemo->setBaseInvoiceFee($baseInvoiceFee);
        $creditmemo->setInvoiceFee($invoiceFee);

        $creditmemo->setBaseGrandTotal( ($creditmemo->getBaseGrandTotal() + $baseInvoiceFee) );
        $creditmemo->setGrandTotal( ($creditmemo->getGrandTotal() + $invoiceFee) );


        if (!$invoiceFeeTax ) {
            return $this;
        }

        $creditmemo->setBaseTaxAmount(
            $creditmemo->getBaseTaxAmount() + $baseInvoiceFeeTax
        );
        $creditmemo->setTaxAmount(
            $creditmemo->getTaxAmount() + $invoiceFeeTax
        );

        return $this;
    }
}
