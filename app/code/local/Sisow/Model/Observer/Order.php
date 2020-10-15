<?php
class Sisow_Model_Observer_Order extends Mage_Core_Model_Abstract
{

    public function salesOrderLoadAfter($observer)
    {    
        $order = $observer->getOrder();
        $payment = $order->getPayment();
        
        $payments = $order->getAllPayments();
        
        foreach($payments as $payment)
        {
            switch($payment->getMethod())
            {
                case 'sisow':
                    $payment->setMethod('sisow_ideal');
                    break;
                case 'sisowklarna':
                    $payment->setMethod('sisow_klarna');
                    break;
                case 'sisowklaacc':
                    $payment->setMethod('sisow_klarnaacc');
                    break;
                case 'sisowob':
                    $payment->setMethod('sisow_overboeking');
                    break;
                case 'sisowcc':
                    $payment->setMethod('sisow_visa');
                    break;
                case 'sisoweb':
                    $payment->setMethod('sisow_ebill');
                    break;
                case 'sisowde':
                    $payment->setMethod('sisow_sofort');
                    break;
                case 'sisowmc':
                    $payment->setMethod('sisow_mistercash');
                    break;
                case 'sisowwg':
                    $payment->setMethod('sisow_webshop');
                    break;
                case 'sisowpp':
                    $payment->setMethod('sisow_paypalec');
                    break;
            }
            
            $payment->Save();
        }
    }
}
