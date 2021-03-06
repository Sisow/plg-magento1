<?php
class Sisow_Model_Methods_Mistercash extends Sisow_Model_Methods_Abstract
{
    protected $_code = 'sisow_mistercash'; //sisow = modulenaam, ideal = paymentcode sisow
    protected $_paymentcode = 'mistercash';
    
    //blocks for loading templates in checkout
    protected $_formBlockType = 'sisow/paymentmethod_default';
    protected $_infoBlockType = 'sisow/paymentmethod_defaultInfo';
    
    protected $_isGateway                 = true;
    protected $_canUseCheckout          = true;
	protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
}    
