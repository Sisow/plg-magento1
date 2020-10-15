<?php
class Sisow_Helper_Data extends Mage_Payment_Helper_Data
{
    public function getPaymentTitle($method) 
    {
        $return = '';

        $paymentCode = strtolower($method->getCode());
        $paymentTitle = $method->getTitle();
        
        $isShowImg = Mage::getStoreConfig('sisow_core/gatewayimage');

        if ($isShowImg) {    		
            $fileWithPath = 'sisow' . DS . 'logo' . DS . $paymentCode . '.' . 'png';
            $iconFileDir = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . $fileWithPath;
            if (file_exists($iconFileDir)) {
                $iconFileUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $fileWithPath;
            }
			else if($paymentCode == 'sisow_klarna'){
				$iconFileUrl = 'https://x.klarnacdn.net/payment-method/assets/badges/generic/klarna.svg';
			}
        }

        echo (empty($iconFileUrl) ? '' : '<img src="' . $iconFileUrl . '" id="' . 'sisow_' . $paymentCode . '" title="' . $paymentTitle . '" height="20px" />&nbsp;');
    }
    
    public function GetNewMailConfig($method)
    {
        $config = Mage::getStoreConfig('payment/'.$method.'/newordermail');
        
        if(empty($config) || $config == "general")
        {
            Mage::log('method: ' . $method, null, 'log_sisow_mail.log');    
            return Mage::getStoreConfig('sisow_core/newordermail');
        }
        else
            return $config;
    }
}
