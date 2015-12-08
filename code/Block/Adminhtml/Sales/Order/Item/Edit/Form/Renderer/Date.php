<?php

class DigitalSession_Mbdt_Block_Adminhtml_Sales_Order_Item_Edit_Form_Renderer_Date
    extends Mage_Catalog_Block_Product_View_Options_Type_Date
{
   /**
     * JS Calendar html
     *
     * @return string Formatted Html
     */
    public function getCalendarDateHtml()
    {
        $value = $this->helper('digitalsession_mbdt')->getItemOptionValueById($this->getOption()->getId());
        if( is_array($value) ) {
            $value = $value['date_internal'];
        }

        $require = '';

        $yearStart = Mage::getSingleton('catalog/product_option_type_date')->getYearStart();
        $yearEnd = Mage::getSingleton('catalog/product_option_type_date')->getYearEnd();

        $calendar = $this->getLayout()
            ->createBlock('core/html_date')
            ->setId('options_'.$this->getOption()->getId().'_date')
            ->setName('options['.$this->getOption()->getId().'][date]')
            ->setClass('product-custom-option datetime-picker input-text' . $require)
            ->setImage($this->getSkinUrl('images/calendar.gif'))
            ->setFormat(Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT))
            ->setValue($value)
            ->setYearsRange('[' . $yearStart . ', ' . $yearEnd . ']');

        return $calendar->getHtml();
    }

    /**
     * Date (dd/mm/yyyy) html drop-downs
     *
     * @return string Formatted Html
     */
    public function getDropDownsDateHtml()
    {
        $value = $this->helper('digitalsession_mbdt')->getItemOptionValueById($this->getOption()->getId());

        if( $value instanceof Zend_Date ) {
            $valueArray = array(
                'year'  => (int) $value->get(Zend_Date::YEAR),
                'month'  => (int) $value->get(Zend_Date::MONTH),
                'day'  => (int) $value->get(Zend_Date::DAY),
                'hour'  => (int) $value->get(Zend_Date::HOUR_AM),
                'minute'  => (int) $value->get(Zend_Date::MINUTE),
                'day_part'  => strtolower($value->get(Zend_Date::MERIDIEM, 'en_US')),
            );

            $value = $valueArray;
        } elseif( is_string($value) ) {
            $value = date_parse($value);
        }


        $this->_dateArray = $value;

        $fieldsSeparator = '&nbsp;';
        $fieldsOrder = Mage::getSingleton('catalog/product_option_type_date')->getConfigData('date_fields_order');
        $fieldsOrder = str_replace(',', $fieldsSeparator, $fieldsOrder);

        $monthsHtml = $this->_getSelectFromToHtml('month', 1, 12, is_array($value) ? $value['month'] : null);
        $daysHtml = $this->_getSelectFromToHtml('day', 1, 31, is_array($value) ? $value['day'] : null);

        $yearStart = Mage::getSingleton('catalog/product_option_type_date')->getYearStart();
        $yearEnd = Mage::getSingleton('catalog/product_option_type_date')->getYearEnd();
        $yearsHtml = $this->_getSelectFromToHtml('year', $yearStart, $yearEnd, is_array($value) ? $value['year'] : null);

        $translations = array(
            'd' => $daysHtml,
            'm' => $monthsHtml,
            'y' => $yearsHtml
        );
        return strtr($fieldsOrder, $translations);
    }

    /**
     * Time (hh:mm am/pm) html drop-downs
     *
     * @return string Formatted Html
     */
    public function getTimeHtml()
    {
        if (Mage::getSingleton('catalog/product_option_type_date')->is24hTimeFormat()) {
            $hourStart = 0;
            $hourEnd = 23;
            $dayPartHtml = '';
        } else {
            $hourStart = 1;
            $hourEnd = 12;
            $dayPartHtml = $this->_getHtmlSelect('day_part', $this->_dateArray['day_part'])
                ->setOptions(array(
                    'am' => Mage::helper('catalog')->__('AM'),
                    'pm' => Mage::helper('catalog')->__('PM')
                ))
                ->getHtml();
        }
        $hoursHtml = $this->_getSelectFromToHtml('hour', $hourStart, $hourEnd, isset($this->_dateArray['hour']) ? intval($this->_dateArray['hour']) : null);
        $minutesHtml = $this->_getSelectFromToHtml('minute', 0, 59, isset($this->_dateArray['minute']) ? intval($this->_dateArray['minute']) : null);

        return $hoursHtml . '&nbsp;<b>:</b>&nbsp;' . $minutesHtml . '&nbsp;' . $dayPartHtml;
    }

    /**
     * HTML select element
     *
     * @param string $name Id/name of html select element
     * @return Mage_Core_Block_Html_Select
     */
    protected function _getHtmlSelect($name, $value = null)
    {
        $option = $this->getOption();

        // $require = $this->getOption()->getIsRequire() ? ' required-entry' : '';
        $require = '';
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setId('options_' . $this->getOption()->getId() . '_' . $name)
            ->setClass('product-custom-option datetime-picker' . $require)
            ->setExtraParams()
            ->setName('options[' . $option->getId() . '][' . $name . ']');

        $extraParams = 'style="width:auto"';

        if( $this->getOption()->getPrice() > 0 ) {
            $extraParams .= ' disabled="true" ';
        }

        $select->setExtraParams($extraParams);

        if (is_null($value)) {
            $value = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $option->getId() . '/' . $name);
        }
        if (!is_null($value)) {
            $select->setValue($value);
        }

        return $select;
    }
}
