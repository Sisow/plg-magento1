<?php
class Sisow_Block_Paymentmethod_Overboeking extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('sisow/checkout/overboeking_form.phtml');
        parent::_construct();
    }
    
    public function getFee()
    {    
        return $this->getMethod()->getFeeArray();
    }
    
    public function getInstructions()
    {
        return $this->getMethod()->getPaymentInstructions();
    }
}
