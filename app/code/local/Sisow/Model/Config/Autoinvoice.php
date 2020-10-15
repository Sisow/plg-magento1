<?php
/**
 * Used in creating options for config value selection
 *
 */
class Sisow_Model_Config_Autoinvoice
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Use General settings')),
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('No')),
            array('value' => 2, 'label'=>Mage::helper('adminhtml')->__('Yes')),
            array('value' => 3, 'label' => Mage::helper('adminhtml')->__('Yes') . ' ' . Mage::helper('adminhtml')->__('and send mail'))
        );
    }

}
