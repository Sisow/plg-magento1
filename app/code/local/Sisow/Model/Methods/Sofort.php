<?php
class Sisow_Model_Methods_Sofort extends Sisow_Model_Methods_Abstract
{
    protected $_code = 'sisow_sofort'; //sisow = modulenaam, ideal = paymentcode sisow
    protected $_paymentcode = 'sofort';
    
    //blocks for loading templates in checkout
    protected $_formBlockType = 'sisow/paymentmethod_default';
    protected $_infoBlockType = 'sisow/paymentmethod_defaultInfo';
    
    protected $_isGateway                 = true;
    protected $_canUseCheckout          = true;
	protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
}    
