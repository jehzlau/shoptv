<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FullPageCache_Block_Adminhtml_Blocks extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_block';
        $this->_blockGroup = 'bubble_fpc';
        $this->_headerText = Mage::helper('bubble_fpc')->__('Manage AJAX Blocks');
        $this->_addButtonLabel = Mage::helper('bubble_fpc')->__('Add New AJAX Block');
        parent::__construct();
        $this->setTemplate('bubble/fpc/widget/grid/container.phtml');
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/newBlock');
    }

    public function getHeaderCssClass()
    {
        return '';
    }
}