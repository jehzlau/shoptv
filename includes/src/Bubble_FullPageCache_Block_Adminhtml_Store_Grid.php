<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FullPageCache_Block_Adminhtml_Store_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('fpcStoreGrid');
        $this->setDefaultSort('store_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setVarNameFilter('fpc_store_filter');
        $this->setUseAjax(true);
    }

    public function getId()
    {
        return 'fpc_tabs_stores_section_content';
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('core/store')
            ->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('store_id', array(
            'header'         => $this->__('Store Id'),
            'index'          => 'store_id',
            'type'           => 'number',
        ));

        $this->addColumn('code', array(
            'header'         => $this->__('Code'),
            'index'          => 'code',
            'type'           => 'text',
        ));

        $this->addColumn('name', array(
            'header'         => $this->__('Name'),
            'index'          => 'name',
            'type'           => 'text',
        ));

        $this->addColumn('is_active', array(
            'header'         => $this->__('Status'),
            'filter'         => false,
            'sortable'       => false,
            'width'          => '80px',
            'align'          => 'center',
            'type'           => 'options',
            'options'        => array(
                '1' => Mage::helper('adminhtml')->__('Yes'),
                '0' => Mage::helper('adminhtml')->__('No'),
            ),
            'frame_callback' => array($this, 'decorateStatus'),
        ));

        $this->addColumn('action',
            array(
                'header'     => Mage::helper('adminhtml')->__('Action'),
                'width'      => '100px',
                'align'      => 'center',
                'type'       => 'action',
                'getter'     => 'getId',
                'actions'    => array(
                    array(
                        'caption' => $this->__('Edit'),
                        'url'     => array(
                            'base' => '*/*/editStore',
                        ),
                        'field'   => 'id',
                    ),
                    array(
                        'caption' => $this->__('Generate'),
                        'url'     => array(
                            'base' => '*/*/generate',
                        ),
                        'field'   => 'store_id',
                        'target'=>	'_blank',
                    ),
                    array(
                        'caption' => $this->__('Clear'),
                        'url'     => array(
                            'base' => '*/*/clearStore',
                        ),
                        'field'   => 'store_id',
                        'confirm'   => Mage::helper('adminhtml')->__('Are you sure?')
                    ),
                ),
                'filter'     => false,
                'sortable'   => false,
                'renderer'   => 'bubble_fpc/adminhtml_widget_grid_column_renderer_action',
            )
        );

        return parent::_prepareColumns();
    }

    public function decorateStatus($value, $row)
    {
        $isActive = class_exists('Bubble_FPC') && Bubble_FPC::isStoreEnabled($row->getCode());
        $class = $isActive ? 'grid-severity-notice' : 'grid-severity-critical';
        $value = $isActive ? 'Enabled' : 'Disabled';

        return '<span class="' . $class . '"><span>' . $this->__($value) . '</span></span>';
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/editStore', array(
            'id' => $row->getId()
        ));
    }
}