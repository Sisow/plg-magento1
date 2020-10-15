<?php

class Sisow_Model_Config_Newordermail
{

    public function toOptionArray()
    {
        return array(
            array(
                "value" => "after_confirmation",
                "label" => Mage::helper('adminhtml')->__("After order confirmation")
            ),
            array(
                "value" => "after_notify_with_cancel",
                "label" => Mage::helper('adminhtml')->__("After notification, including cancelled order")
            ),
            array(
                "value" => "after_notify_without_cancel",
                "label" => Mage::helper('adminhtml')->__("After notification, excluding cancelled order")
            )
        );
    }

}
