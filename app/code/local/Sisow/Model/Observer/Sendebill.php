<?php
class Sisow_Model_Observer_Sendebill
{
    public function sendEbill(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        $storeId = $order->getStore()->getId();
        
		if($order->getPayment()->getMethodInstance()->getCode() == 'sisow_overboeking' || ($order->getPayment()->getMethodInstance()->getCode() == 'sisow_ebill' && !(bool)Mage::getStoreConfig('payment/sisow_ebill/sendoninvoice',$storeId)))
			$this->Send($order, round($order->getGrandTotal(), 2), false);
        
        return $this;
    }
	
	public function sendEbillInvoice(Varien_Event_Observer $observer)
    {
		$_event = $observer->getEvent();
		$_invoice = $_event->getInvoice();
		$_order = $_invoice->getOrder();
        $storeId = $_order->getStore()->getId();

		if ($_invoice->getUpdatedAt() == $_invoice->getCreatedAt() && $_order->getPayment()->getMethodInstance()->getCode() == 'sisow_ebill' && (bool)Mage::getStoreConfig('payment/sisow_ebill/sendoninvoice',$storeId))
			$this->Send($_order, round($_invoice->getGrandTotal(), 2), true);
    }
	
	private function Send($order, $amount, $onInvoice)
	{
        if($order->getPayment()->getMethodInstance()->getCode() == 'sisow_ebill' || $order->getPayment()->getMethodInstance()->getCode() == 'sisow_overboeking')
        {
            $storeId = $order->getStore()->getId();

            if(!Mage::app()->getStore()->isAdmin())
                return $this;
            
            $arg = array();
            $base = Mage::getModel('sisow/base');
            $base->switchStore($storeId);
            
            $base->payment = $order->getPayment()->getMethodInstance()->getCode() == 'sisow_ebill' ? 'ebill' : 'overboeking';
            
            $arg['billing_firstname'] = $order->getBillingAddress()->getFirstname();
            $arg['billing_lastname'] = $order->getBillingAddress()->getLastname();
            $arg['billing_countrycode'] = $order->getBillingAddress()->getCountry();
            $arg['testmode'] = (Mage::getStoreConfig('payment/'.$order->getPayment()->getMethodInstance()->getCode().'/testmode', $storeId)) ? 'true' : 'false';
            
            
            if($order->getPayment()->getMethodInstance()->getCode() == 'sisow_overboeking')
            {
                $arg['days'] = Mage::getStoreConfig('payment/sisow_overboeking/days', $storeId);
                $arg['including'] = (Mage::getStoreConfig('payment/sisow_overboeking/include', $storeId)) ? 'true' : 'false';
            }
            else
                $arg['days'] = Mage::getStoreConfig('payment/sisow_ebill/days', $storeId);
            
            $arg['billing_mail'] = $order->getBillingAddress()->getEmail();
            if(empty($arg['billing_mail']))
                $arg['billing_mail'] = $order->getCustomerEmail();
            
            $base->amount = $amount;
            $base->purchaseId = $order->getCustomerId() . $order->getRealOrderId();
            $base->entranceCode = str_replace('-', '', $order->getRealOrderId());
            
            $base->description = $order->getRealOrderId();
            
            $invalidchar = strpos($order->getRealOrderId(), '-') !== FALSE ? 'true' : 'false';
			
			if(!$onInvoice)
				$base->notifyUrl = Mage::getUrl('sisow/checkout/notify', array('_secure' => true, 'invalidchar' => $invalidchar));
			else
				$base->notifyUrl = Mage::getUrl('sisow/checkout/notify', array('_secure' => true, 'invalidchar' => $invalidchar, 'oninvoice' => 'true'));
			
            $base->returnUrl = Mage::getBaseUrl();
            
            if(($ex = $base->TransactionRequest($arg)) < 0)
            {
                Mage::getSingleton('adminhtml/session')->addError('Sisow error: ' . $ex . ', ' . $base->errorCode);
                return $this;
            }    
            
            $payment = $order->getPayment();
            $comm = 'Sisow Ebill created.<br />';
            $comm .= 'Transaction ID: ' . $base->trxId . '<br/>';
            $st = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
            $payment->setAdditionalInformation('trxId', $base->trxId)
                ->setAdditionalInformation('documentId', $base->documentId)
                ->save();
				
			if(!$onInvoice)
				$order->setState($st, $st, $comm);
			
            $order->save();
                    
			if(!$onInvoice)		
				$order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
			
            $order->getPayment()->setAdditionalInformation('trxId', $base->trxId)->save();
                
            $transaction = Mage::getModel('sales/order_payment')
                        ->setMethod($order->getPayment()->getMethodInstance()->getCode())
                        ->setTransactionId($base->trxId)
                        ->setIsTransactionClosed(false);
                        
            $order->setPayment($transaction);
            $transaction->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH);    
            $order->save();    
            
            Mage::getSingleton('adminhtml/session')->addSuccess('The Ebill/Overboeking has been created and send to the customer.');
        }
        
        return $this;
	}
}
