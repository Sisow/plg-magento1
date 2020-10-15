<?php
class Sisow_Model_Observer_Paymentfee
{
    public function salesQuoteCollectTotalsAfter(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $quote->setInvoiceFee(0);
        $quote->setBaseInvoiceFee(0);
        $quote->setInvoiceFeeExcludedVat(0);
        $quote->setBaseInvoiceFeeExcludedVat(0);
        $quote->setInvoiceTaxAmount(0);
        $quote->setBaseInvoiceTaxAmount(0);
        $quote->setInvoiceFeeRate(0);

        foreach ($quote->getAllAddresses() as $address) {
            $quote->setInvoiceFee(
                (float) $quote->getInvoiceFee() + $address->getInvoiceFee()
            );
            $quote->setBaseInvoiceFee(
                (float) $quote->getBaseInvoiceFee() + $address->getBaseInvoiceFee()
            );

            $quoteFeeExclVat = $quote->getInvoiceFeeExcludedVat();
            $addressFeeExclCat = $address->getInvoiceFeeExcludedVat();
            $quote->setInvoiceFeeExcludedVat(
                (float) $quoteFeeExclVat + $addressFeeExclCat
            );

            $quoteBaseFeeExclVat = $quote->getBaseInvoiceFeeExcludedVat();
            $addressBaseFeeExclVat = $address->getBaseInvoiceFeeExcludedVat();
            $quote->setBaseInvoiceFeeExcludedVat(
                (float) $quoteBaseFeeExclVat + $addressBaseFeeExclVat
            );

            $quoteFeeTaxAmount = $quote->getInvoiceTaxAmount();
            $addressFeeTaxAmount = $address->getInvoiceTaxAmount();
            $quote->setInvoiceTaxAmount(
                (float) $quoteFeeTaxAmount + $addressFeeTaxAmount
            );

            $quoteBaseFeeTaxAmount = $quote->getBaseInvoiceTaxAmount();
            $addressBaseFeeTaxAmount = $address->getBaseInvoiceTaxAmount();
            $quote->setBaseInvoiceTaxAmount(
                (float) $quoteBaseFeeTaxAmount + $addressBaseFeeTaxAmount
            );
            $quote->setInvoiceFeeRate($address->getInvoiceFeeRate());
        }
    }
    
    public function salesOrderPaymentPlaceEnd(Varien_Event_Observer $observer) 
    {
        $payment = $observer->getPayment();
        if (substr($payment->getMethodInstance()->getCode(), 0, 5) != 'sisow') {
            return;
        }

        $info = $payment->getMethodInstance()->getInfoInstance();
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if (!$quote->getId()) {
            $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }

        //Set the payment fee included tax value
        $info->setAdditionalInformation('invoice_fee', $quote->getInvoiceFee());
        $info->setAdditionalInformation('base_invoice_fee', $quote->getBaseInvoiceFee());
        $info->setAdditionalInformation('invoice_fee_exluding_vat', $quote->getInvoiceFeeExcludedVat());
        $info->setAdditionalInformation('base_invoice_fee_exluding_vat', $quote->getBaseInvoiceFeeExcludedVat());
        //Set the payment fee tax amount
        $info->setAdditionalInformation('invoice_tax_amount', $quote->getInvoiceTaxAmount());
        $info->setAdditionalInformation('base_invoice_tax_amount', $quote->getBaseInvoiceTaxAmount());
        //Set the payment fee rate used
        $info->setAdditionalInformation('invoice_fee_rate', $quote->getInvoiceFeeRate());

        $info->save();
    }
}
