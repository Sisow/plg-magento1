<?php
class Sisow_Model_Methods_Eps extends Sisow_Model_Methods_Abstract
{
    protected $_code = 'sisow_eps'; //sisow = modulenaam, ideal = paymentcode sisow
    protected $_paymentcode = 'eps';
    
    //blocks for loading templates in checkout
    protected $_formBlockType = 'sisow/paymentmethod_eps';
    //protected $_infoBlockType = 'sisow/paymentmethod_idealInfo';
    
    protected $_isGateway                 = true;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canUseCheckout          = true;
    
    public function getOrderPlaceRedirectUrl()
    {
        $bic = Mage::app()->getRequest()->getParam('payment')['sisow_eps_bic'];
        
        /*
		 * Redirect to Sisow
		 * method = paymentcode from Sisow
		 * additional params (fields from the checkout page)
		*/
        $url = Mage::getUrl('sisow/checkout/redirect/', array('_secure' => true));
        if (strpos($url, "?") === false) $url .= '?';
        else $url .= '&';
        $url .= 'bic='.$bic.'&method=eps';
        return $url;
    }
}    
