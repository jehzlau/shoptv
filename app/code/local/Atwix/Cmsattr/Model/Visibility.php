<?php

class Atwix_Cmsattr_Model_Visibility extends Mage_Catalog_Model_Product_Visibility {

    public function addVisibleInCatalogFilterToCollection(Mage_Eav_Model_Entity_Collection_Abstract $collection)
    {
        parent::addVisibleInCatalogFilterToCollection($collection);
        Mage::getSingleton('cataloginventory/instockonly')->addInStockFilterToCollection($collection);
        return $this;
    }

    public function addVisibleInSearchFilterToCollection(Mage_Eav_Model_Entity_Collection_Abstract $collection)
    {
        parent::addVisibleInSearchFilterToCollection($collection);
        Mage::getSingleton('cataloginventory/instockonly')->addInStockFilterToCollection($collection);
        return $this;
    }

    public function addVisibleInSiteFilterToCollection(Mage_Eav_Model_Entity_Collection_Abstract $collection)
    {
        parent::addVisibleInSiteFilterToCollection($collection);
        Mage::getSingleton('cataloginventory/instockonly')->addInStockFilterToCollection($collection);
        return $this;
    }

}
