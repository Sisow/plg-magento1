<?php
class Sisow_Block_Paymentmethod_Ideal extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('sisow/checkout/ideal_form.phtml');
        parent::_construct();
    }
    
    /**
     * Loading the avaible issuers
     *
     * @return array
     */
    public function getIssuers()
    {
        $issuers = '';
        Mage::getModel('sisow/base')->DirectoryRequest($issuers, false, (boolean)Mage::getStoreConfig('payment/sisow_ideal/testmode'));
        return $issuers;
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
