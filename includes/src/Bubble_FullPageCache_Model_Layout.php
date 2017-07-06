<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FullPageCache_Model_Layout extends Varien_Object
{
    /**
     * @var Mage_Core_Model_Store
     */
    protected $_store;

    /**
     * @var Mage_Customer_Model_Session
     */
    protected $_session;

    /**
     * Initialization
     */
    protected function _construct()
    {
        $this->_store = Mage::app()->getStore();
        $this->_session = Mage::getSingleton('customer/session');
        if ($pollId = Mage::getSingleton('core/session')->getJustVotedPoll()) {
            $this->_session->setLastVotedPoll($pollId);
        }
    }

    /**
     * @return $this
     */
    protected function _addLoggedInUser()
    {
        if ($this->_session->isLoggedIn()) {
            $this->setLoggedIn(true);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function _addCurrency()
    {
        $currentCurrencyCode = $this->_store->getCurrentCurrencyCode();
        if ($currentCurrencyCode != $this->_store->getDefaultCurrencyCode()) {
            $this->setCurrency($currentCurrencyCode);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function _addCustomerGroup()
    {
        if ($groupId = $this->_session->getCustomerGroupId()) {
            $this->setCustomerGroup($groupId);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function _addCartItems()
    {
        $items = array();
        $quoteItems = Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();
        foreach ($quoteItems as $item) {
            $items[] = array(
                'product_id'    => $item->getProductId(),
                'qty'           => $item->getQty(),
                'total'         => (float) $item->getRowTotal(),
            );
        }
        if (!empty($items)) {
            $this->setCartItems($items);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function _addWishlistItems()
    {
        /** @var $helper Mage_Wishlist_Helper_Data */
        $helper = Mage::helper('wishlist');
        if ($helper->isAllow()) {
            $items = array();
            $wishlistItems = $helper->getWishlistItemCollection();
            foreach ($wishlistItems as $item) {
                $productId = (int) $item->getProductId();
                $items[$productId] = array(
                    'product_id'    => $productId,
                    'store_id'      => (int) $item->getStoreId(),
                    'product_name'  => (string) $item->getProductName(),
                    'name'          => (string) $item->getName(),
                    'price'         => (float) $item->getPrice(),
                );
            }
            ksort($items);
            if (!empty($items)) {
                $this->setWishlistItems($items);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function _addCompareItems()
    {
        $items = Mage::helper('catalog/product_compare')->getItemCollection()->getAllIds();
        if (!empty($items)) {
            sort($items);
            $this->setCompareItems($items);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function _addRecentlyComparedItems()
    {
        $items = Mage::app()->getLayout()
            ->createBlock('reports/product_compared')
            ->getItemsCollection()
            ->getAllIds();
        if (!empty($items)) {
            $this->setRecentlyComparedItems($items);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function _addPoll()
    {
        $pollId = $this->_session->getLastVotedPoll();
        if ($pollId) {
            $answers = Mage::getModel('poll/poll_answer')
                ->getResourceCollection()
                ->addPollFilter($pollId)
                ->load()
                ->walk('getVotesCount');
            $this->setLastVotedPoll(array(
                'poll_id' => (int) $pollId,
                'answers' => $answers,
            ));
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        $this->_addLoggedInUser();
        $this->_addCurrency();
        $this->_addCustomerGroup();
        $this->_addCartItems();
        $this->_addWishlistItems();
        $this->_addCompareItems();
        $this->_addRecentlyComparedItems();
        $this->_addPoll();

        Mage::dispatchEvent('bubble_fpc_layout_params', array('layout' => $this));

        return $this->getData();
    }
}