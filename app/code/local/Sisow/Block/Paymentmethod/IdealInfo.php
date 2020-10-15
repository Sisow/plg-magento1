<?php
class Sisow_Block_Paymentmethod_IdealInfo extends Mage_Payment_Block_Info
{
    /**
     * Constructor. Set template.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('sisow/checkout/ideal_info.phtml');
    }

    /**
     * Returns code of payment method
     *
     * @return string
     */
    public function getMethodCode()
    {
        return $this->getInfo()->getMethodInstance()->getCode();
    }
    
    /**
     * Returns the payment fee
     *
     * @return array
     */
    public function getFee()
    {    
        if(Mage::getSingleton('core/session')->getSisowFeeInc())
            $fee_array['incl'] = Mage::getSingleton('core/session')->getSisowFeeInc();
        else
            $fee_array = $this->getMethod()->getFeeArray();
        
        return $fee_array;
    }
    
    public function _getAdditionalInfo()
    {
        $info = $this->getInfo()->getMethodInstance()->getInfoInstance();
        $customer = array();
        $customer['consumerName'] = $info->getAdditionalInformation('consumerName');
        $customer['consumerIban'] = $info->getAdditionalInformation('consumerIban');
        $customer['consumerBic'] = $info->getAdditionalInformation('consumerBic');

        return $customer;
    }
}
