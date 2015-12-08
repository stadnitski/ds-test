<?php

class DigitalSession_Mbdt_Block_Adminhtml_Sales_Order_Item_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form

{
    public function _construct()
    {
        parent::_construct();
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('item_id' => $this->getRequest()->getParam('item_id'))),
                'method' => 'post',
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('form_form',
                array('legend'=>Mage::helper('digitalsession_mbdt')->__('Configure Product Custom Options')));

        /**
         * We're showing all the options as form field... Who
         * knows what else we're going to add to this form later
         */
        $fieldset->addType('options_renderer',
                'DigitalSession_Mbdt_Block_Adminhtml_Sales_Order_Item_Edit_Form_Renderer');

        $fieldset->addField('options_form', 'options_renderer', array(
                'label'     => Mage::helper('digitalsession_mbdt')->__('Custom Options'),
                'required'  => false,
                'name'      => 'options_form',
                'rendered_html' => $this->_getOptionsRendered(),
            ));

        $fieldset->addField('qty', 'text', array(
            'label'     => Mage::helper('digitalsession_mbdt')->__('Qty Ordered'),
            'required'  => false,
            'name'      => 'qty',
            'readonly'  => true,
            'value'     => $this->_getOrderItem()->getQtyOrdered(),

        ));

        return parent::_prepareForm();
    }


    protected function _getOptionsRendered()
    {
        $returnHtml = '';
        $optionsBlock = $this->getChild('product_options');
        $options = Mage::registry('current_product')->getOptions();

        foreach($options as $_option) {
            $returnHtml .= $optionsBlock->getOptionHtml($_option);
        }

        return $returnHtml;
    }

    /**
     * @return Mage_Sales_Model_Order_Item
     */
    protected function _getOrderItem()
    {
        return Mage::registry('current_order_item');
    }
}