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
class AdjustWare_Cartalert_Model_Mysql4_Stoplist extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('adjcartalert/stoplist', 'id');
    }
    
    public function contains($groupId, $email)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('s' => $this->getMainTable()),'id')
            ->where('s.store_id = ?',  $groupId)
            ->where('s.customer_email = ?',  $email)
            ->limit(1);
        return $this->_getReadAdapter()->fetchOne($select);
    }     
}