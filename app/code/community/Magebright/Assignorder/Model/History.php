<?php

class Magebright_Assignorder_Model_History extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('assignorder/history');
    }
   
}