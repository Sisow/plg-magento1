<?php
abstract class Sisow_Model_Methods_Abstract extends Mage_Payment_Model_Method_Abstract
{
    protected $_isGateway               = false;
    protected $_canAuthorize            = false;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = false;
    protected $_canRefundInvoicePartial = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = false;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc                 = false;
	
	protected $_paybyinvoice = false;
    
    public function getQuote() 
    {
        return $this->getCheckout()->getQuote();
    }

    public function getCheckout() 
    {
        return Mage::getSingleton('checkout/session');
    }
    
    public function getOrderPlaceRedirectUrl()
    {        
        /*
		 * Redirect to Sisow
		 * method = paymentcode from Sisow
		 * additional params (fields from the checkout page)
		*/
        $url = Mage::getUrl('sisow/checkout/redirect/', array('_secure' => true));
        if (strpos($url, "?") === false) $url .= '?';
        else $url .= '&';
        $url .= 'method='.$this->_paymentcode;
        return $url;
    }
    
    public function getPaymentInstructions()
    {
        return Mage::getStoreConfig('payment/sisow_'.$this->_paymentcode.'/instructions');
    }
                
    public function capture(Varien_Object $payment, $amount)
    {        
		// nothing to capture
		if(!$this->_paybyinvoice)
			return $this;
	
		$paymentCode = $payment->getMethodInstance()->getCode();
		
		$sendKlarnaInvoice = Mage::getStoreConfig('payment/sisow_klarna/sendklarnainvoice');
		$sendAfterpayInvoice = Mage::getStoreConfig('payment/sisow_afterpay/sendafterpayinvoice');
		$sendFocumInvoice = Mage::getStoreConfig('payment/sisow_focum/sendfocuminvoice');
	
		if($paymentCode == 'sisow_klarna' && (empty($sendKlarnaInvoice) || $sendKlarnaInvoice == 1))
			return $this;
		else if($paymentCode == 'sisow_afterpay' && (empty($sendAfterpayInvoice) || $sendAfterpayInvoice == 1))
			return $this;
		else if($paymentCode == 'sisow_focum' && (empty($sendFocumInvoice) || $sendFocumInvoice == 1))
			return $this;

        if($payment->getAdditionalInformation('sisowmakeinvoicesuccess')){
            return $this;
        }

		$base = Mage::getModel('sisow/base');
        $trxid = $payment->getAdditionalInformation('trxId');

		if(($ex = $base->InvoiceRequest($trxid)) < 0)
		{
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('sisow')->__("Sisow Invoice can't be created") . '!');
            return;
		}

        $payment->setAdditionalInformation('sisowmakeinvoicesuccess', true)->save();
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('sisow')->__("Sisow Invoice created") . '!');

        $comm = 'Sisow Factuurnummer: '. $base->invoiceNo;

        $order = $payment->getOrder();
        $order->addStatusHistoryComment($comm);
        $order->save();

        return $this;
    }
	
	/**
     * Set capture transaction ID to invoice for informational purposes
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function processInvoice($invoice, $payment)
    {
        $invoice->setTransactionId($payment->getAdditionalInformation('trxId'));
        return $this;
    }
    
    /**
     * Refund a capture transaction
     *
     * @param Varien_Object $payment
     * @param float $amount
     */
    public function refund(Varien_Object $payment, $amount)
    {
		$trxid = $this->_getParentTransactionId($payment);
		
        if($trxid)
        {
            $base = Mage::getModel('sisow/base');
            $base->amount = $amount;
            if(($ex = $base->RefundRequest($trxid)) < 0)
            {
                Mage::log($trxid . ': Sisow RefundRequest failed('.$ex.', '.$base->errorCode.', '.$base->errorMessage.')', null, 'log_sisow.log');
            }
            else
            {    
                $order = $payment->getOrder();
                $transaction = Mage::getModel('sales/order_payment')
                            ->setMethod($this->_code)
                            ->setTransactionId($ex)
                            ->setIsTransactionClosed(true);
                            
                $order->setPayment($transaction);
                $transaction->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND);
                        
                $order->save();    
            }
        }
        else
        {
            Mage::log($trxid . ': refund failed no transactionId found', null, 'log_sisow.log');
            Mage::throwException(Mage::helper('sisow')->__("Impossible to issue a refund transaction because the transactionId can't be loaded."));
        }
        
        return $this;
    }

    protected function _getParentTransactionId(Varien_Object $payment)
    {
        return $payment->getParentTransactionId() ? $payment->getParentTransactionId() : $payment->getLastTransId();
    }
}
