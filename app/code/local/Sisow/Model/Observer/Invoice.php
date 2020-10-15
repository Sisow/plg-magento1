<?php
class Sisow_Model_Observer_Invoice
{
    public function makeInvoice(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
        $sendKlarnaInvoice = Mage::getStoreConfig('payment/sisow_klarna/sendklarnainvoice');
        $sendAfterpayInvoice = Mage::getStoreConfig('payment/sisow_afterpay/sendafterpayinvoice');

        $makeInvoice = ($paymentCode == 'sisow_afterpay' && (!empty($sendAfterpayInvoice) && $sendAfterpayInvoice > 1));
        $makeInvoice |=  ($paymentCode == 'sisow_klarna' && (!empty($sendKlarnaInvoice) && $sendKlarnaInvoice > 1));

        if ($makeInvoice) {
            $ostate = $order->getState();
            $ostatus = $order->getStatus();

            $mStatus = Mage::getStoreConfig('payment/'.$paymentCode.'/make_invoice_on_status');
            if (!$mStatus  || $ostatus != $mStatus) {
                return;
            }

            $payment = $order->getPayment();
            $payment->getMethodInstance()->capture($payment,round($order->getGrandTotal(), 2));
        }
    }
}