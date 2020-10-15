<?php
class Sisow_Model_Observer_Submit
{
    public function sales_model_service_quote_submit_after(Varien_Event_Observer $observer) 
    {
        $method = $observer->getEvent()->getOrder()->getPayment()->getMethod();
        if (substr($method, 0, 6) == 'sisow_') {
            if (Mage::getStoreConfig('sisow_core/keepcart')) {
                $observer->getQuote()->setIsActive(TRUE);
            }
        }
    }
}
