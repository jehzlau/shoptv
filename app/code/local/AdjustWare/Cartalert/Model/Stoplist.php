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
class AdjustWare_Cartalert_Model_Stoplist extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('adjcartalert/stoplist');
    }
    
    /**
     * Checks if email exists in stoplist
     *
     * @param int $groupId 
     * @param string $email eamil to check
     * @return bool
     */
    public function contains($groupId, $email)
    {
        return $this->_getResource()->contains($groupId, $email);
    }      
}