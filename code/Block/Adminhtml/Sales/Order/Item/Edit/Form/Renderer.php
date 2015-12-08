<?php

class DigitalSession_Mbdt_Block_Adminhtml_Sales_Order_Item_Edit_Form_Renderer
    extends Varien_Data_Form_Element_Abstract

{
    protected $_element;

    public function getElementHtml()
    {
        return $this->getData('rendered_html');
    }
}