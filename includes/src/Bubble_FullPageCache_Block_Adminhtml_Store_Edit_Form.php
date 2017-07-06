<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FullPageCache_Block_Adminhtml_Store_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('fpc_store_form');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'store'     => $this->getData('store'),
            'method'    => 'post'
        ));

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('bubble_fpc')->__('Store View Information')
        ));

        $store = Mage::registry('fpc_store');

        if ($store && $store->getId()) {
            $fieldset->addField('store_id', 'hidden', array(
                'name' => 'store_id',
            ));
        }

        $fieldset->addField('is_active', 'select',
            array(
                'name'      => 'is_active',
                'label'     => Mage::helper('bubble_fpc')->__('Enable Cache'),
                'class'     => 'required-entry',
                'required'  => true,
                'values'    => array(
                    '1' => Mage::helper('adminhtml')->__('Yes'),
                    '0' => Mage::helper('adminhtml')->__('No'),
                ),
            )
        );

        if ($store) {
            $form->addValues(array(
                'store_id'  => $store->getId(),
                'is_active' => class_exists('Bubble_FPC') && Bubble_FPC::isStoreEnabled($store->getCode()),
            ));
        }

        $form->setAction($this->getUrl('*/*/saveStore'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}