<?php
/**
 * Payment fee on order invoice
 *
 * Class to handle the payment fee on a order Invoice pdf
 *
 */

class Sisow_Model_Paymentfee_Invoice_Total extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{

    /**
     * Collect the order total
     *
     * @param object $invoice The invoice instance to collect from
     *
     * @return Mage_Sales_Model_Order_Invoice_Total_Abstract
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {        
        $order = $invoice->getOrder();
        $method = $order->getPayment()->getMethodInstance();

        if (substr($method->getCode(), 0, 5) != 'sisow') {
            return $this;
        }

        // Only collect the invoice fee if we do not have any recent invoices
        if ($invoice->getOrder()->hasInvoices() != 0) {
            return $this;
        }


        $invoiceFee = $order->getSisowFeeInclTax()  != null ? $order->getSisowFeeInclTax() : $order->getSisowFee()+$order->getSisowFeeTax();
        $baseInvoiceFee = $order->getBaseSisowFeeInclTax()  != null ? $order->getBaseSisowFeeInclTax() : $order->getBaseSisowFee()+$order->getBaseSisowFeeTax();
        $invoiceFeeExludingVat = $order->getSisowFee();
        $baseInvoiceFeeExludingVat =  $order->getBaseSisowFee();

        /*
        $info = $order->getPayment()->getMethodInstance()->getInfoInstance();
        $invoiceFee =  $info->getAdditionalInformation('invoice_fee');
        $baseInvoiceFee =  $info->getAdditionalInformation('base_invoice_fee');
        $invoiceFeeExludingVat = $info->getAdditionalInformation('invoice_fee_exluding_vat');
        $baseInvoiceFeeExludingVat = $info->getAdditionalInformation('base_invoice_fee_exluding_vat');
        */

        if (!$invoiceFee) {
            return $this;
        }


        if ($invoice->isLast()) {
            //The tax for our invoice fee is already applied to the grand total
            //at this point, so we only need to add the remaining  amount
            $invoice->setBaseGrandTotal(
                $invoice->getBaseGrandTotal() + $baseInvoiceFeeExludingVat
            );
            $invoice->setGrandTotal(
                $invoice->getGrandTotal() + $invoiceFeeExludingVat
            );
        } else {
            //Our tax doesn't get picked up by the parent function so we need
            //to add our complete invoice fee
            $invoice->setBaseGrandTotal(
                $invoice->getBaseGrandTotal() + $baseInvoiceFee
            );
            $invoice->setGrandTotal($invoice->getGrandTotal() + $invoiceFee);
        }

        $invoice->setBaseInvoiceFee($baseInvoiceFee);
        $invoice->setInvoiceFee($invoiceFee);

        $order->setBaseInvoiceFeeInvoiced($invoiceFeeExludingVat);
        $order->setInvoiceFeeInvoiced($invoiceFee);
        
        return $this;
    }

}
