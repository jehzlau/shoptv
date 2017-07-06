<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FullPageCache_Block_Adminhtml_Stores extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_store';
        $this->_blockGroup = 'bubble_fpc';
        $this->_headerText = Mage::helper('bubble_fpc')->__('Manage Store Views');
        parent::__construct();
        $this->_removeButton('add');
        $this->setTemplate('bubble/fpc/widget/grid/container.phtml');
        $this->_addButton('generate', array(
            'label'     => Mage::helper('bubble_fpc')->__('Generate All'),
            'onclick'   => 'window.open(\'' . $this->getGenerateAllUrl() .'\')',
            'class'     => 'go',
        ));
    }

    public function getGenerateAllUrl()
    {
        return $this->getUrl('*/*/generateAll');
    }

    public function getCreateUrl()
    {
        return '';
    }

    public function getHeaderCssClass()
    {
        return '';
    }
}