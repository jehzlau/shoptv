<?php
/**
 * Abandoned Carts Alerts Pro
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.6.2
 * @license:     HURXKDG7XXFyzvUh6YIaOzBxXgSru0OEdm0JvUmsP0
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
class AdjustWare_Cartalert_Block_Adminhtml_History_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id'; // ?
        $this->_blockGroup = 'adjcartalert';
        $this->_controller = 'adminhtml_history';

        $this->_removeButton('reset');
        $this->_removeButton('save');
    }

    public function getHeaderText()
    {
            return Mage::helper('adjcartalert')->__('Sent Alert');
    }
}