<?php

class Magebright_Assignorder_Model_Mysql4_History extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {            
        $this->_init('assignorder/history', 'assignorder_id');
    }
}