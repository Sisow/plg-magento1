<?php
class Sisow_Model_Methods_Visa extends Sisow_Model_Methods_Abstract
{
    protected $_code = 'sisow_visa'; //sisow = modulenaam, ideal = paymentcode sisow
    protected $_paymentcode = 'visa';
    
    //blocks for loading templates in checkout
    protected $_formBlockType = 'sisow/paymentmethod_default';
    protected $_infoBlockType = 'sisow/paymentmethod_defaultInfo';
    
    protected $_isGateway                 = true;
    protected $_canUseCheckout          = true;
	protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    
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
        $url .= '&method=visa';
        return $url;
    }
}    
