<?php

class Atwix_Cmsattr_Model_Stock {

    /**
     * Add a statusInStock requirement for visibility
     */
    public function addInStockFilterToCollection($collection)
    {
        if($websiteId = Mage::app()->getWebsite()->getWebsiteId()) {
            $collection->joinField(
                'stock_status',
                'cataloginventory/stock_status',
                'stock_status',
                'product_id=entity_id', array(
                    'stock_status' => Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK,
                    'website_id' => $websiteId
                )
            );
        }
    }

}
