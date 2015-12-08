<?php

class DigitalSession_Mbdt_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getItemOptionValueById($optionId, $orderItem=null)
    {
        /** @var Mage_Sales_Model_Order_Item $orderItem */
        if( !$orderItem instanceof Varien_Object ) {
            $orderItem = Mage::registry('current_order_item');
        }

        $optionsArray = $orderItem->getProductOptions();

        foreach( $optionsArray['options'] as $_option ) {
            if( isset($_option['option_id']) && $_option['option_id'] == $optionId ) {
                return $_option['option_value'];
            }
        }

        return false;
    }
}