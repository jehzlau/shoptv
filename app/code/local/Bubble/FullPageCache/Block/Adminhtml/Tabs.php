<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FullPageCache_Block_Adminhtml_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('fpc_tabs');
        $this->setTitle(Mage::helper('bubble_fpc')->__('Full Page Cache'));
    }

    protected function _beforeToHtml()
    {
        $activeSection = $this->_getActiveSection();

        $this->addTab('stores_section', array(
            'label'     => Mage::helper('bubble_fpc')->__('Store Views'),
            'title'     => Mage::helper('bubble_fpc')->__('Store Views'),
            'url'       => $this->getUrl('*/*/stores', array('_current' => true)),
            'class'     => 'ajax',
            'active'    => $activeSection === 'stores',
        ));

        $this->addTab('actions_section', array(
            'label'     => Mage::helper('bubble_fpc')->__('Cacheable Actions'),
            'title'     => Mage::helper('bubble_fpc')->__('Cacheable Actions'),
            'url'       => $this->getUrl('*/*/actions', array('_current' => true)),
            'class'     => 'ajax',
            'active'    => $activeSection === 'actions',
        ));

        $this->addTab('blocks_section', array(
            'label'     => Mage::helper('bubble_fpc')->__('AJAX Blocks'),
            'title'     => Mage::helper('bubble_fpc')->__('AJAX Blocks'),
            'url'       => $this->getUrl('*/*/blocks', array('_current' => true)),
            'class'     => 'ajax',
            'active'    => $activeSection === 'blocks',
        ));

        return parent::_beforeToHtml();
    }

    protected function _getActiveSection($default = 'stores')
    {
        $activeSection = Mage::getSingleton('adminhtml/session')->getActiveSection();
        if (!$activeSection) {
            $activeSection = $default;
        }

        return $activeSection;
    }
}