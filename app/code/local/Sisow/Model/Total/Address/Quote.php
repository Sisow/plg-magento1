<?php
class Sisow_Model_Total_Address_Quote extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    private $storeid;

	public function __construct()
	{
		$this->setCode('sisow_fee');
		$this->storeid = Mage::app()->getStore()->getStoreId();
	}
	 
	public function collect(Mage_Sales_Model_Quote_Address $address)
	{
		$quote = $address->getQuote();
		
		if (!$quote->isVirtual() && $address->getAddressType() == 'billing'){
			return $this;
		}
		
		// sisow method?
		$paymentcode = $quote->getPayment()->getMethod();
		if(strpos($paymentcode, 'sisow_') === false){
			return $this;
		}
		
		// get fee
		$feeSetting = Mage::getStoreConfig('payment/' . $paymentcode . '/payment_fee', $this->storeid);
		
		// no fee?
		if(empty($feeSetting)){
			return $this;
		}
		
		$feeAmount = 0;
		
		// calc fee
		if(strpos($feeSetting, ';') !== false){
			$feeParts = explode(';', $feeSetting);
			
			// invalid settings
			if(count($feeParts) != 2){
				return $this;
			}
			
			foreach($feeParts as $singleFee){
				$feeAmount += $this->getFee($singleFee, $address);
			}
		}
		else{
			$feeAmount += $this->getFee($feeSetting, $address);
		}
		
		// no fee?
		if($feeAmount <= 0){
			return $this;
		}
		 
		$baseFee = $feeAmount;
				
		$fee = Mage::app()->getStore()->convertPrice($baseFee);
		
		$address->setBaseSisowFee($baseFee);
		$address->setSisowFee($fee);
		 		 
		$address->setBaseGrandTotal($address->getBaseGrandTotal() + $baseFee);
		$address->setGrandTotal($address->getGrandTotal() + $fee);
		return $this;
	}
	
	public function fetch(Mage_Sales_Model_Quote_Address $address)
	{
        // $this->storeid
        $taxOption = Mage::getStoreConfig("tax/cart_display/shipping", $this->storeid);

		$title = Mage::helper('sisow')->__('Payment fee');
        if (($taxOption === '1') || ($taxOption === '3')) {
            $label = $title;
            $amount = $address->getSisowFee();
            if ($taxOption == '3') {
                $label .= ' (Excl.Tax)';
            }
            if ($amount != 0) {
                $address->addTotal(array(
                    'code' => $this->getCode(),
                    'title' => $label,
                    'value' => $amount
                ));
            }
        }

        if (($taxOption === '2') || ($taxOption === '3')) {
            $label = $title;
            $amount = $address->getSisowFeeInclTax();

            if ($taxOption == '3') {
                $label .= ' (Incl.Tax)';
            }
            if ($amount != 0) {
                $address->addTotal(array(
                    'code' => $this->getCode().'_tax',
                    'title' => $label,
                    'value' => $amount
                ));
            }
        }
		return $this;
	}

	private function getFee($feeSetting, Mage_Sales_Model_Quote_Address $address){
		if($feeSetting > 0){
			return $feeSetting;
		}
		else if($feeSetting < 0){
			return round($address->getBaseSubtotalInclTax() * (($feeSetting * -1) / 100) ,2);
		}
		else{
			return 0;
		}
	}
}