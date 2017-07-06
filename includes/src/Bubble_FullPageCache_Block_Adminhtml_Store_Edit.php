<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FullPageCache_Block_Adminhtml_Store_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'bubble_fpc';
        $this->_controller = 'adminhtml_store';

        $this->_updateButton('save', 'label', Mage::helper('bubble_fpc')->__('Save Store View'));
        $this->_removeButton('delete');
    }

    public function getHeaderText()
    {
        $store = Mage::registry('fpc_store');
        if ($store && $store->getId()) {
            return Mage::helper('bubble_fpc')->__(
                "Edit Store View '%s' (%s, %d)", $store->getName(), $store->getCode(), $store->getId()
            );
        }

        return '';
    }

    public function getHeaderCssClass()
    {
        return '';
    }

    public function getFormBlockUrl()
    {
        return $this->getUrl('*/*/saveStore');
    }

    public function getDeleteUrl()
    {
        return '';
    }
}
