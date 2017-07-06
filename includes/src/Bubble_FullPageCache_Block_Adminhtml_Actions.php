<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FullPageCache_Block_Adminhtml_Actions extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_action';
        $this->_blockGroup = 'bubble_fpc';
        $this->_headerText = Mage::helper('bubble_fpc')->__('Manage Cacheable Actions');
        $this->_addButtonLabel = Mage::helper('bubble_fpc')->__('Add New Action');
        parent::__construct();
        $this->setTemplate('bubble/fpc/widget/grid/container.phtml');
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/newAction');
    }

    public function getHeaderCssClass()
    {
        return '';
    }
}