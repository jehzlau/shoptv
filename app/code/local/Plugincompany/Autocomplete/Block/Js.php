<?php
/**
 *
 * Created by:  Milan Simek
 * Company:     Plugin Company
 *
 * LICENSE: http://plugin.company/docs/magento-extensions/magento-extension-license-agreement
 *
 * YOU WILL ALSO FIND A PDF COPY OF THE LICENSE IN THE DOWNLOADED ZIP FILE
 *
 * FOR QUESTIONS AND SUPPORT
 * PLEASE DON'T HESITATE TO CONTACT US AT:
 *
 * SUPPORT@PLUGIN.COMPANY
 *
 */
class Plugincompany_Autocomplete_Block_Js extends Mage_Core_Block_Template {

    public function getLastCacheRefresh()
    {
        $fileName = Mage::getBaseDir() . DS . 'media' . DS . 'suggestioncache' . DS . Mage::app()->getStore()->getStoreId() . '_1.json';
        $time = filemtime($fileName);
        if (!$time) {
            return 'false';
        }
        return $time;
    }
}