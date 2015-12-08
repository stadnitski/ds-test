<?php

class DigitalSession_Mbdt_Block_Adminhtml_Sales_Order_Item_Edit_Form_Footer
    extends Mage_Adminhtml_Block_Abstract
{
    public function _prepareLayout()
    {
        $this->setChild('save_button', $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                'label' => Mage::helper('digitalsession_mbdt')->__('Save Options'),
                'class' => 'save',
                'id' => 'save_options_button',
                'onclick' => 'edit_form.request({onComplete : parent.Mbdt.refreshItemsList()})'
            )));

        $this->setChild('cancel_button', $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                'label' => Mage::helper('digitalsession_mbdt')->__('Cancel'),
                'class' => 'cancel',
                'onclick' => 'parent.Mbdt.closePopup()',
            )));
        return parent::_prepareLayout();
    }

    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    public function getCancelButtonHtml()
    {
        return $this->getChildHtml('cancel_button');
    }
}