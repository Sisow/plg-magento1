<?php
class Sisow_Model_Methods_Overboeking extends Sisow_Model_Methods_Abstract
{
    protected $_code = 'sisow_overboeking'; //sisow = modulenaam, ideal = paymentcode sisow
    protected $_paymentcode = 'overboeking';
    
    //blocks for loading templates in checkout
    protected $_formBlockType = 'sisow/paymentmethod_overboeking';
    protected $_infoBlockType = 'sisow/paymentmethod_defaultInfo';
    
    protected $_isGateway                 = true;
    protected $_canUseCheckout          = true;
    protected $_canUseInternal          = true;
	protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
}    
