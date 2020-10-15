<?php
class Sisow_Model_Methods_Ebill extends Sisow_Model_Methods_Abstract
{
    protected $_code = 'sisow_ebill'; //sisow = modulenaam, ideal = paymentcode sisow
    protected $_paymentcode = 'ebill';
    
    //blocks for loading templates in checkout
    protected $_formBlockType = 'sisow/paymentmethod_default';
    protected $_infoBlockType = 'sisow/paymentmethod_defaultInfo';
    
    protected $_isGateway                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
	protected $_canCapturePartial       = true;
	
	public function canUseCheckout()
    {
        return (bool)Mage::getStoreConfig('payment/sisow_ebill/useincheckout');
    }
}    
