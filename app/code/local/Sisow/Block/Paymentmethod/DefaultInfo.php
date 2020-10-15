<?php
class Sisow_Block_Paymentmethod_DefaultInfo extends Mage_Payment_Block_Info
{
    /**
     * Constructor. Set template.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('sisow/checkout/default_info.phtml');
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
        $a_info = array();
        $a_info['consumerName'] = $info->getAdditionalInformation('consumerName');
        $a_info['consumerIban'] = $info->getAdditionalInformation('consumerIban');
        $a_info['consumerBic'] = $info->getAdditionalInformation('consumerBic');
        $a_info['trxid'] = $info->getAdditionalInformation('trxId');

        return $a_info;
    }
}
