<?php

class DigitalSession_Mbdt_Adminhtml_MbdtController extends Mage_Adminhtml_Controller_Action
{
    public function popupAction()
    {
        $this->loadLayout();

        $itemId = $this->getRequest()->getParam('item_id');
        try {
            /** @var Mage_Sales_Model_Order_Item $orderItem */
            $orderItem = Mage::getModel('sales/order_item')->load($itemId);
            if( !$orderItem->getId() ) {
                $this->_redirect('*/*/error');
                return;
            }

            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::getModel('catalog/product')->load($orderItem->getProductId());

            if( !$product->getOptions() ) {
                $this->_redirect('*/*/error');
                return;
            }

            Mage::register('current_product', $product);
            Mage::register('current_order_item', $orderItem);
        } catch( Exception $e ) {
            $this->_redirect('*/*/error');
            return;
        }

        $this->renderLayout();
    }

    public function saveAction()
    {
        $itemId = $this->getRequest()->getParam('item_id');

        try {
            /** @var Mage_Sales_Model_Order_Item $orderItem */
            $orderItem = Mage::getModel('sales/order_item')->load($itemId);
            if( !$orderItem->getId() ) {
                $this->_redirect('*/*/error');
                return;
            }

            $product = Mage::getModel('catalog/product')->load($orderItem->getProductId());
            $processMode = Mage_Catalog_Model_Product_Type_Abstract::PROCESS_MODE_LITE;
            $options = array();
            $requestAsObject = new Varien_Object($this->getRequest()->getParams());


            foreach ($product->getOptions() as $option) {
                /* @var $option Mage_Catalog_Model_Product_Option */
                $factory = $option->groupFactory($option->getType());
                $factory->setOption($option)
                    ->setProduct($product)
                    ->setProcessMode($processMode)
                    ->validateUserValue($requestAsObject->getOptions());

                $optionValue = $requestAsObject->getData('options/' . $option->getId());

                if( !$optionValue ) {
                    /**
                     * If the form element has been read-only, than we just copy option from previous data
                     */
                    $oldOption = $this->_findOptionById($orderItem->getProductOptions(), $option->getId());
                } else {
                    $oldOption = null;
                }
                /**
                 * Fixes Zend_Date mysterious issue...
                 */
                if( $option->getData('type') == 'date_time' ) {
                    foreach( $optionValue as $key => $value ) {
                        if( empty($optionValue[$key]) ) {
                            $optionValue[$key] = '0';
                        }
                    }

                    if( strtolower($optionValue['day_part']) == 'pm' ) {
                            $optionValue['hour'] += 12;
                    }

                    $timestamp = mktime($optionValue['hour'], $optionValue['minute'], $optionValue['second'], $optionValue['month'], $optionValue['day'], $optionValue['year']);

                    $date = new Zend_Date($timestamp);
                    $optionValue = $date;
                }

                if( is_array($oldOption) ) {
                    $options[] = $oldOption;
                } else {
                    $options[] = array(
                        'label' => $option->getTitle(),
                        'value' => $factory->getFormattedOptionValue($optionValue),
                        'print_value' => $factory->getPrintableOptionValue($optionValue),
                        'option_id' => $option->getId(),
                        'option_type' => $option->getType(),
                        'custom_view' => $factory->isCustomizedView(),
                        'option_value' => $optionValue,
                    );
                }
            }

            $orderItemsOrigArray = $orderItem->getProductOptions();
            $orderItem->setProductOptions(array('options' => $options, 'info_buyRequest' => $orderItemsOrigArray['info_buyRequest']))->save();

            $order = Mage::getModel('sales/order')->load($orderItem->getOrderId());
            $order->setInvokeSavingProcess(true); // Trick that helps to invoke order saving process
            $order->save();

            //Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('digitalsession_mbdt')->__('Options has been successfully saved.'));
            $this->_redirect('*/*/success');
        } catch( Exception $e ) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('digitalsession_mbdt')->__($e->getMessage()));
            $this->_redirect('*/*/popup', array('item_id' => $itemId));
            return;
        }

    }

    public function successAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function errorAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * TODO: add all this to layout XML file
     */
    public function getItemsListAction()
    {
        /** @var Mage_Adminhtml_Block_Sales_Order_View_Items $itemsBlock */
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);

        $listBlock = $this->getLayout()->createBlock('core/text_list');
        $itemsBlock = $this->getLayout()->createBlock('adminhtml/sales_order_view_items');
        $listBlock->append($itemsBlock);
        $listBlock->addData(array('order' => $order));

        $itemsBlock->setTemplate('sales/order/view/items.phtml')
            ->addItemRender('default', 'adminhtml/sales_order_view_items_renderer_default',
            'sales/order/view/items/renderer/default.phtml')
            ->addColumnRender('qty', 'adminhtml/sales_items_column_qty', 'sales/items/column/qty.phtml')
            ->addColumnRender('name', 'adminhtml/sales_items_column_name', 'sales/items/column/name.phtml')
            ->addColumnRender('name', 'adminhtml/sales_items_column_name_grouped', 'sales/items/column/name.phtml', 'grouped');

        $this->getResponse()->setBody($itemsBlock->toHtml());
    }

    protected function _findOptionById($options, $optionId)
    {
        if( !isset($options['options']) ) {
            return false;
        }

        foreach($options['options'] as $_option) {
            if( $_option['option_id'] == $optionId ) {
                return $_option;
            }
        }

        return false;
    }
}