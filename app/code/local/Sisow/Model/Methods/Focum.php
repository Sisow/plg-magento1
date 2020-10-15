<?php
class Sisow_Model_Methods_Focum extends Sisow_Model_Methods_Abstract
{
    protected $_code = 'sisow_focum'; //sisow = modulenaam, ideal = paymentcode sisow
    protected $_paymentcode = 'focum';
    
    //blocks for loading templates in checkout
    protected $_formBlockType = 'sisow/paymentmethod_focum';
    protected $_infoBlockType = 'sisow/paymentmethod_defaultInfo';
    
    protected $_isGateway               = true;
    protected $_canUseCheckout          = true;
	protected $_canCapture 				= true;
    
    public function getOrderPlaceRedirectUrl()
    {
        $gender = Mage::app()->getRequest()->getParam('payment')['sisow_gender'];
        $phone = Mage::app()->getRequest()->getParam('payment')['sisow_phone'];
        $day = Mage::app()->getRequest()->getParam('payment')['sisow_day'];
        $month = Mage::app()->getRequest()->getParam('payment')['sisow_month'];
        $year = Mage::app()->getRequest()->getParam('payment')['sisow_year'];
        $iban = Mage::app()->getRequest()->getParam('payment')['sisow_iban'];
        
        $dob = $day . $month . $year;
        
        /*
		 * Redirect to Sisow
		 * method = paymentcode from Sisow
		 * additional params (fields from the checkout page)
		*/
        $url = Mage::getUrl('sisow/checkout/redirect/', array('_secure' => true));
        if (strpos($url, "?") === false) $url .= '?';
        else $url .= '&';
        $url .= 'method=focum';
        $url .= '&gender='.$gender;
        $url .= '&phone='.$phone;
        $url .= '&dob='.$dob;
        $url .= '&iban='.$iban;
        
        return $url;
    }
    
    public function getPhone()
    {
        $phone = $this->getQuote()->getBillingAddress()->getTelephone();
        if (!$phone && $this->getQuote()->getShippingAddress())
            $phone = $this->getQuote()->getShippingAddress()->getTelephone();
        return $phone;
    }
}    