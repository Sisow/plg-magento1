<?php
class Sisow_Block_Paymentmethod_Klarnaacc extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('sisow/checkout/klarnaacc_form.phtml');
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
    
    public function getPhone() 
    {
        return $this->getMethod()->getPhone();
    }
    
    public function getDates()
    {
        $days = array();

        for($i=1;$i<32;$i++)
            $days[sprintf("%02d", $i)] = sprintf("%02d", $i);
        
        return $days;
    }
    
    public function GetCountryIso()
    {
        return $this->GetQuote()->getBillingAddress()->getCountryId();
    }
    
    public function getMonths()
    {
        $months = array();
        $months['01'] = $this->__('January');
        $months['02'] = $this->__('February');
        $months['03'] = $this->__('March');
        $months['04'] = $this->__('April');
        $months['05'] = $this->__('May');
        $months['06'] = $this->__('June');
        $months['07'] = $this->__('July');
        $months['08'] = $this->__('August');
        $months['09'] = $this->__('September');
        $months['10'] = $this->__('October');
        $months['11'] = $this->__('November');
        $months['12'] = $this->__('December');
        return $months;
    }
    
    public function getYears()
    {        
        $year = array();
        for($i=(date("Y")-15);$i>(date("Y")-115);$i--)
            $year[$i] = $i;
        
        return $year;
    }
    
    public function getMonthly()
    {
        return $this->getMethod()->getMonthly();
    }
    
    public function getPclass()
    {
        return $this->getMethod()->getPclass();
    }
    
    private function GetQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }
}
