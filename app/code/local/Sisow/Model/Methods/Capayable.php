<?php
class Sisow_Model_Methods_Capayable extends Sisow_Model_Methods_Abstract
{
    protected $_code = 'sisow_capayable'; //sisow = modulenaam, ideal = paymentcode sisow
    protected $_paymentcode = 'capayable';
    
    //blocks for loading templates in checkout
    protected $_formBlockType = 'sisow/paymentmethod_capayable';
    protected $_infoBlockType = 'sisow/paymentmethod_defaultInfo';
    
    protected $_isGateway                 = true;
    protected $_canUseCheckout          = true;
    
    public function getOrderPlaceRedirectUrl()
    {
        $gender = Mage::app()->getRequest()->getParam('payment')['sisow_gender'];
        $phone = Mage::app()->getRequest()->getParam('payment')['sisow_phone'];
        $day = Mage::app()->getRequest()->getParam('payment')['sisow_day'];
        $month = Mage::app()->getRequest()->getParam('payment')['sisow_month'];
        $year = Mage::app()->getRequest()->getParam('payment')['sisow_year'];
        
        $dob = $day . $month . $year;
        
        /*
		 * Redirect to Sisow
		 * method = paymentcode from Sisow
		 * additional params (fields from the checkout page)
		*/
        $url = Mage::getUrl('sisow/checkout/redirect/', array('_secure' => true));
        if (strpos($url, "?") === false) $url .= '?';
        else $url .= '&';
        $url .= 'method=capayable';
        $url .= '&gender='.$gender;
        $url .= '&phone='.$phone;
        $url .= '&dob='.$dob;
        
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