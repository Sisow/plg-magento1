<?php

class Sisow_Block_Sales_Ordertotal extends Mage_Sales_Block_Order_Totals
{
	public function getSisowFee(){
		$order = $this->getOrder();
		return $order->getSisowFee();
	}

	public function getBaseSisowFee(){
		$order = $this->getOrder();
		return $order->getBaseSisowFee();
	}

	public function initTotals(){
		$amount = $this->getSisowFee();

		if(floatval($amount)){
			$total = new Varien_Object();
			$total->setCode('sisow_fee');
			$total->setValue($amount);
			$total->setBaseValue($this->getBaseSisowFee());
			$total->setLabel('Payment fee');
			$parent = $this->getParentBlock();
			$parent->addTotal($total,'subtotal');
		}
	}

	public function getOrder(){
		if(!$this->hasData('order')){
			$order = $this->getParentBlock()->getOrder();
			$this->setData('order',$order);
		}
		return $this->getData('order');
	}
}