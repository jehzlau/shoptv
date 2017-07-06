<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FullPageCache_Block_Adminhtml_Block_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('fpc_block_form');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('bubble_fpc')->__('Block Information')
        ));

        $block = Mage::registry('fpc_block');

        if ($block && $block->getId()) {
            $fieldset->addField('block_id', 'hidden', array(
                'name' => 'block_id',
            ));
        }

        $fieldset->addField('name', 'text',
            array(
                'name'      => 'name',
                'label'     => Mage::helper('bubble_fpc')->__('Block Name'),
                'note'      => $this->escapeHtml(Mage::helper('bubble_fpc')->__('Specify the block name, not the alias.')),
                'class'     => 'required-entry',
                'required'  => true,
            )
        );

        $fieldset->addField('is_active', 'select',
            array(
                'name'      => 'is_active',
                'label'     => Mage::helper('bubble_fpc')->__('Is Active'),
                'class'     => 'required-entry',
                'required'  => true,
                'values'    => array(
                    '1' => Mage::helper('adminhtml')->__('Yes'),
                    '0' => Mage::helper('adminhtml')->__('No'),
                ),
            )
        );

        if ($block) {
            $form->addValues($block->getData());
        }
        $form->setAction($this->getUrl('*/*/saveBlock'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}