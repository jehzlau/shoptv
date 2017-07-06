<?php
class Atwix_Cmsattr_Block_List extends Mage_Catalog_Block_Product_Abstract
{
    protected $_itemCollection = null;

    public function getItems()
    {
        $brand = $this->getBrand();
        if (!$brand)
            return false;
        if (is_null($this->_itemCollection)) {
            $this->_itemCollection = Mage::getModel('atwix_cmsattr/products')->getItemsCollection($brand);
        }

        return $this->_itemCollection;
    }
}