<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 1/2/14
 * Time: 6:10 PM
 */

class Galore_Shipping_Model_Carrier_Galore
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'galore';
    protected $_isFixed = true;

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {

        // skip if not enabled
        if (!Mage::getStoreConfig('carriers/'.$this->_code.'/active')) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');

        $method = Mage::getModel('shipping/rate_result_method');
        $method->setCarrier($this->_code);
        $method->setCarrierTitle(Mage::getStoreConfig('carriers/'.$this->_code.'/title'));

        /* Computation */
        //Mage::getSingleton('core/session', array('name'=>'frontend'));
        //$session = Mage::getSingleton('checkout/session');
        //$cart_items = $session->getQuote()->getAllVisibleItems();
        //$region = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getData('region');
        //$totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals();
        $visible_items = $this->getAllVisibleItems($request);
        //$package_value = $request->getPackageValue();
        $package_value = $request->getPackageValueWithDiscount();
        $region = strtolower($request->getDestRegionCode()); //use if onepage checkout
        $region_id = strtolower($request->getDestRegionId()); //use if onestep checkout
        $brands = explode("\n", strtoupper($this->getConfigData('diaperbrands')));
        $bulkyfee = (float) $this->getConfigData('bulkyfee');
        $total_bulk_fee = 0.00;
        $flatrate = (float)$this->getConfigData('flatrate');
        $mm_min = (float)$this->getConfigData('mm_min');
        $mm_fee = (float)$this->getConfigData('mm_fee');
        $pr_min = (float)$this->getConfigData('pr_min');
        $pr_fee = (float)$this->getConfigData('pr_fee');
        $shippingFee = 0.00;
        $is_bulky = false;

	    //force region value
	    if($region == '') $region = $region_id;

        foreach ($visible_items as $item) {
            $product = Mage::getModel('catalog/product')->load($item->getProduct_id());
            $product_brand = strtoupper($product->getResource()->getAttribute('brand')->getFrontend()->getValue($product));
            $bulkyAttr = $product->getResource()->getAttribute('is_bulky'); //is_bulky product attribute required.;

            if ($bulkyAttr) {
                $is_bulky = strtolower($bulkyAttr->getFrontend()->getValue($product));
            }

            if (in_array($product_brand, $brands) || $is_bulky == 'yes') {
                $total_bulk_fee += $bulkyfee * $item->getQty();
            }
        }

        if ($region) {
            if ($region == 'metro manila') {
                if ($package_value < $mm_min) {
                    $shippingFee += $mm_fee;
                }
            } else {
                if ($package_value < $pr_min) {
                    $shippingFee += $pr_fee;
                }
            }
        } else {
            if ($package_value < $mm_min)
            $shippingFee += $flatrate;
        }

        $shippingFee += $total_bulk_fee;
        //if free shipping
        if ($request->getFreeShipping() === true) {
            $shippingFee = 0;
        }

        /* Use method name */
        $method->setMethod($this->_code);
        $method->setMethodTitle(Mage::getStoreConfig('carriers/'.$this->_code.'/name'));
        $method->setCost($shippingFee);
        $method->setPrice($shippingFee);
        $result->append($method);

        return $result;
    }

    public function getAllowedMethods()
    {
        return array('galore'=>$this->getConfigData('name'));
    }


    //only get visible items from cart
    public function getAllVisibleItems(Mage_Shipping_Model_Rate_Request $request)
    {
        $items = null;
        foreach ($request->getAllItems() as $requestItem) {
            if (!$requestItem->getParent_item_id() && !$requestItem->isDeleted()) {
                $items[] = $requestItem;
            }
        }
        return $items;
    }
}