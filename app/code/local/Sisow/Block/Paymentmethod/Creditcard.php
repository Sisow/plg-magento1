<?php
class Sisow_Block_Paymentmethod_CreditCard extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('sisow/checkout/creditcard_form.phtml');
        parent::_construct();
    }
    
    /**
     * Loading the avaible issuers
     *
     * @return array
     */
    public function getCctypes()
    {
        $cctypes = array();
        
        if(Mage::getStoreConfig('payment/sisow_creditcard/choose_cc_type'))
        {
            $cctypes['visa'] = 'Visa';
            $cctypes['mastercard'] = 'MasterCard';
            $cctypes['maestro'] = 'Maestro';
        }
    
        return $cctypes;
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
