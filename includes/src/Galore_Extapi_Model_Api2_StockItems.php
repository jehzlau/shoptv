<?php
class Galore_Extapi_Model_Api2_StockItems extends Mage_Api2_Model_Resource
{
    const ITEM_NOT_FOUND_ERROR = "Stock Item Not Found";

    //Get Stock Item by SKU
    protected function getStockItem($sku)
    {
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        return $stockItem;
    }

    protected function _update()
    {
        $bodyParams = $this->getRequest()->getBodyParams();
        $sku = $this->getRequest()->getParam('sku');

        //Get stock item
        $stockItem = $this->getStockItem($sku);

        //Check if stock item exists
        if(!is_object($stockItem) || !$stockItem->getId()){
            $this->_critical('Stock Item for ' . $sku .' not found', Mage_Api2_Model_Server::HTTP_NOT_FOUND);
        }

        //Set update qty of stock item
        foreach($bodyParams as $key => $value){
            $stockItem->setData($key, $value);
        }

        try {
            $stockItem->save();
        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }

    }


    protected function _multiUpdate()
    {

        $bodyParams = $this->getRequest()->getBodyParams();
        $stock_items = $bodyParams['stock_items'];

        //Validate $stock_items
        if(!is_array($stock_items)){
            $this->_critical('Invalid Data', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        //Loop through stock_items
        foreach($stock_items as $item){

            /**
             * Attempt to save stock update
             * Catch Exceptions to display it as response
             * Refer to /app/code/core/Mage/CatalogInvetory/Model/Api2/Stock/Item/Rest
             */
            try {
                //Get stock item properties
                $sku = $item['sku'];

                //Create new array, strip $sku
                $item_properties = array_diff($item, array($sku));

                $stockItem = $this->getStockItem($sku);

                //Validate if stock item exists
                if(!is_object($stockItem) || !$stockItem->getId()){
                    $this->_critical(self::ITEM_NOT_FOUND_ERROR, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                }

                //Proceed with stock item update
                $stockItem =$this->getStockItem($sku);

                foreach($item_properties as $key => $value){
                    $stockItem->setData($key, $value);
                }
                $stockItem->save();

                $this->_successMessage(self::RESOURCE_UPDATED_SUCCESSFUL, Mage_Api2_Model_Server::HTTP_OK, array(
                    'item_id' => $stockItem->getId(),
                    'sku' => isset($sku) ? $sku : null
                ));
            } catch (Mage_Api2_Exception $e) {
                $this->_errorMessage($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR, array(
                    'item_id' => isset($stockItem['item_id']) ? $stockItem['item_id'] : null,
                    'sku' => isset($sku) ? $sku : null
                ));
            } catch (Exception $e) {
                $this->_errorMessage($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR, array(
                    'item_id' => isset($stockItem['item_id']) ? $stockItem['item_id'] : null
                ));
            }
        }
    }

    protected function _retrieve()
    {
        $sku = $this->getRequest()->getParam('sku');
        $stockItem = $this->getStockItem($sku);
        return $stockItem;
    }

}