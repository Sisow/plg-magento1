<?php
class Sisow_Model_Methods_Afterpay extends Sisow_Model_Methods_Abstract
{
    protected $_code = 'sisow_afterpay'; //sisow = modulenaam, ideal = paymentcode sisow
    protected $_paymentcode = 'afterpay';
    
    //blocks for loading templates in checkout
    protected $_formBlockType = 'sisow/paymentmethod_afterpay';
    protected $_infoBlockType = 'sisow/paymentmethod_defaultInfo';
    
    protected $_isGateway               = true;
    protected $_canUseCheckout          = true;
	protected $_canCapture 				= true;

    protected $_paybyinvoice = true;
    
    public function getOrderPlaceRedirectUrl()
    {
        $gender = Mage::app()->getRequest()->getParam('payment')['sisow_gender'];
        $phone = Mage::app()->getRequest()->getParam('payment')['sisow_phone'];
        $day = Mage::app()->getRequest()->getParam('payment')['sisow_day'];
        $month = Mage::app()->getRequest()->getParam('payment')['sisow_month'];
        $year = Mage::app()->getRequest()->getParam('payment')['sisow_year'];
        $coc = array_key_exists('sisow_coc',Mage::app()->getRequest()->getParam('payment')) ? Mage::app()->getRequest()->getParam('payment')['sisow_coc'] : null;

        $dob = $day . $month . $year;
        
        /*
		 * Redirect to Sisow
		 * method = paymentcode from Sisow
		 * additional params (fields from the checkout page)
		*/
        $url = Mage::getUrl('sisow/checkout/redirect/', array('_secure' => true));
        if (strpos($url, "?") === false) $url .= '?';
        else $url .= '&';
        $url .= 'method=afterpay';
        $url .= '&gender='.$gender;
        $url .= '&phone='.$phone;
        $url .= '&dob='.$dob;
        $url .= '&coc='.$coc;
        
        return $url;
    }
    
    public function getPhone()
    {
        $phone = $this->getQuote()->getBillingAddress()->getTelephone();
        if (!$phone && $this->getQuote()->getShippingAddress())
            $phone = $this->getQuote()->getShippingAddress()->getTelephone();
        return $phone;
    }
	
	public function getCountry()
    {
        $country = $this->getQuote()->getBillingAddress()->getCountryId();
        if (!$country && $this->getQuote()->getShippingAddress())
            $country = $this->getQuote()->getShippingAddress()->getCountryId();
        return $country;
    }
	
	public function getCompany()
    {
        $company = $this->getQuote()->getBillingAddress()->getCompany();
        if (!$company && $this->getQuote()->getShippingAddress())
            $company = $this->getQuote()->getShippingAddress()->getCompany();
        return $company;
    }
}    