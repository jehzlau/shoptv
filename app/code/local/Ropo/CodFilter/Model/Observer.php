<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Ropo
 * @package     Ropo_CodFilter
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @generator   http://www.mgt-commerce.com/kickstarter/ Mgt Kickstarter
 */

class Ropo_CodFilter_Model_Observer {
 public function filterpaymentmethod(Varien_Event_Observer $observer) {
    /* call get payment method */
        $method = $observer->getEvent()->getMethodInstance();
        $postDatas =  Mage::app()->getRequest()->getParams();

        //echo $postDatas["region_id"];
        if($method->getCode()=='cashondelivery'){
            
      if(isset($postDatas["region_id"]))
      {

       
            $RegionIdadmin=Mage::getStoreConfig("payment/cashondelivery/specificregion");
            $RegionIdadmin=explode(",", $RegionIdadmin);
           if(in_array($postDatas["region_id"],$RegionIdadmin)){
            $result = $observer->getEvent()->getResult();   
            $result->isAvailable = true;
            return;
            }
            else{
            $result = $observer->getEvent()->getResult();   
            $result->isAvailable = false;
            }
                      
       
        }
        else
        {
            $currentAction=Mage::app()->getRequest()->getActionName();
            if($currentAction=="save_address")
            {
            $result = $observer->getEvent()->getResult();   
            $result->isAvailable = false;
           }
        }
         }
        return;
    }
}
?>