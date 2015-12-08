<?php

class DigitalSession_Mbdt_Block_Adminhtml_Sales_Order_Item_Edit_Form_Renderer_Text
    extends Mage_Catalog_Block_Product_View_Options_Type_Text
{
    public function getDefaultValue()
    {
        return $this->helper('digitalsession_mbdt')
                    ->getItemOptionValueById($this->getOption()->getId());;
    }
}
