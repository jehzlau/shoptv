<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FullPageCache_Model_Observer
{
    /**
     * Shortcut to FPC helper
     *
     * @var Bubble_FullPageCache_Helper_Data $_helper
     */
    protected $_helper;

    /**
     * Does FPC is enabled?
     *
     * @var bool
     */
    protected $_enabled = false;

    /**
     * Flag to see if cache has already been generated or not
     *
     * @var bool
     */
    protected $_cached = false;

    /**
     * Session storages to check for messages
     *
     * @var array
     */
    protected $_storages = array(
        'core/session',
        'customer/session',
        'catalog/session',
        'checkout/session',
        'tag/session'
    );

    public function __construct()
    {
        $this->_helper = Mage::helper('bubble_fpc');
        $this->_enabled = $this->_helper->isFpcEnabled();
    }

    /**
     * Store cache for current page if possible and update layout hash
     *
     * @param Varien_Event_Observer $observer
     */
    public function onHttpResponseSendBefore(Varien_Event_Observer $observer)
    {
        $response = $observer->getEvent()->getResponse();
        if ($this->_helper->isCacheable() && $this->_helper->isStoreCacheable()) {
            Mage::getSingleton('core/translate')->setTranslateInline(false);
            if ($this->_helper->isActionCacheable() && !$this->_cached) {
                $body = $response->getBody();

                // AJAX blocks
                $pattern = '#<!-- fpc start (.*?) -->(.*?)<!-- fpc end -->#s';
                $body = preg_replace($pattern, '<div id="$1" class="fpc-block"></div>', $body);

                $this->_helper->saveLayout($body);
                $this->_cached = true;
            }

            if ($response->canSendHeaders(false)) {
                $response->setHeader('Pragma', 'no-cache', true)
                    ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0', true);
            }
        }

        $this->_helper->updateCookieStore();
        $this->_helper->updateCookieLayout();
    }

    /**
     * Catch messages block to handle them in a cookie
     *
     * @param Varien_Event_Observer $observer
     */
    public function onCoreBlockToHtmlAfter(Varien_Event_Observer $observer)
    {
        if ($this->_helper->isCacheable() && $this->_helper->isStoreCacheable() && !$this->_cached) {
            /* @var $block Mage_Core_Block_Abstract */
            $block = $observer->getEvent()->getBlock();
            if (in_array($block->getNameInLayout(), $this->_helper->getCookieBlocks())) {
                if ($block->getNameInLayout() == 'global_messages') {
                    $block->setClearCookie(true);
                } else {
                    $html = $observer->getTransport()->getHtml();
                    $html = $this->_helper->cleanHtml($html);
                    $this->cookify($block->getNameInLayout(), $html);
                }
                $block->setTemplate('bubble/fpc/cookie.phtml');
                $observer->getTransport()->setHtml($block->renderView());
            }

            // AJAX blocks
            $blockName = $block->getNameInLayout();
            if (in_array($blockName, $this->_helper->getAjaxBlocks())) {
                // Handle blocks that may have same name repeated several times in the page
                static $blocks = array();
                if (!isset($blocks[$blockName])) {
                    $blocks[$blockName] = 0;
                } else {
                    $blockName .= '_' . ++$blocks[$blockName];
                }

                // Add placeholder around block
                $html = $observer->getTransport()->getHtml();
                $html = "<!-- fpc start $blockName -->$html<!-- fpc end -->";
                $observer->getTransport()->setHtml($html);
            }
        }
    }

    /**
     * Handle welcome message for Magento prior to 1.8
     *
     * @param Varien_Event_Observer $observer
     */
    public function onCoreBlockToHtmlBefore(Varien_Event_Observer $observer)
    {
        if ($this->_helper->isCacheable() && $this->_helper->isStoreCacheable() && !$this->_cached) {
            /* @var $block Mage_Core_Block_Abstract */
            $block = $observer->getEvent()->getBlock();
            if ($block instanceof Mage_Page_Block_Html_Header && version_compare(Mage::getVersion(), '1.8') === -1) {
                $this->cookify('welcome', $block->getWelcome());
                $cookieBlock = Mage::app()->getLayout()
                    ->createBlock('core/template', 'welcome')
                    ->setTemplate('bubble/fpc/cookie.phtml');
                $block->setWelcome($cookieBlock->renderView());
            }
        }

        $request = Mage::app()->getRequest();
        if ($request->isXmlHttpRequest() && $request->getParam('fpc')) {
            /* @var $block Mage_Core_Block_Abstract */
            $block = $observer->getEvent()->getBlock();
            $blockName = $block->getNameInLayout();
            if (in_array($blockName, $this->_helper->getAjaxBlocks())) {
                // Handle blocks that may have same name repeated several times in the page
                static $blocks = array();
                if (!isset($blocks[$blockName])) {
                    $blocks[$blockName] = 0;
                } else {
                    $blockName .= '_' . ++$blocks[$blockName];
                }

                $block->setFrameTags($blockName);
            }
        }
    }

    public function onLayoutRenderBefore()
    {
        $request = Mage::app()->getRequest();
        if (!$request->isXmlHttpRequest() || !$request->getParam('fpc')) {
            $this->prepareMessages();

            return false;
        }

        $start = microtime(true);

        Mage::app()->setUseSessionInUrl(false);
        $_SERVER['REQUEST_URI'] = parse_url($request->getRequestUri(), PHP_URL_PATH);

        $layout = Mage::app()->getLayout();
        $html = $layout->getOutput();
        $output = array(
            'blocks' => array(),
        );
        $blocks = explode(',', $request->getParam('blocks'));
        foreach ($blocks as $blockName) {
            $pattern = sprintf('#<%s>(.*?)</%s>#s', $blockName, $blockName);
            preg_match($pattern, $html, $matches);
            if (isset($matches[1])) {
                $output['blocks'][$blockName] = $matches[1];
            }
        }

        $output['took'] = microtime(true) - $start;

        Mage::app()->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->setBody(Mage::helper('core')->jsonEncode($output))
            ->sendResponse();
        exit;
    }

    /**
     * @param $name
     * @param $content
     * @return Mage_Core_Model_Cookie
     */
    public function cookify($name, $content)
    {
        return $this->_helper->cookie()->set($name, json_encode($content), true, null, null, null, false);
    }

    /**
     * Save messages in cookie to allow full page caching
     *
     * @param bool $force
     */
    public function prepareMessages($force = false)
    {
        if ($this->_helper->isCacheable() || true === $force) {
            $messages = Mage::getModel('core/message_collection');
            foreach ($this->_storages as $storage) {
                $session = Mage::getSingleton($storage);
                foreach ($session->getMessages(true)->getItems() as $message) {
                    $messages->add($message);
                }
            }
            if ($messages->count()) {
                $html = Mage::app()->getLayout()
                    ->getMessagesBlock()
                    ->setMessages($messages)
                    ->getGroupedHtml();
                $this->cookify('global_messages', $html);
            }
        }
    }

    /**
     * Disable session storage of catalog navigation parameters
     */
    public function disableCatalogSession()
    {
        Mage::getSingleton('catalog/session')->setParamsMemorizeDisabled(true);
    }

    /**
     * Clear FPC when cache clearing is asked
     */
    public function onCoreCleanCache()
    {
        $this->_helper->clearCache();
    }

    /**
     * Clear FPC if no tags are specified on cache clearing
     *
     * @param Varien_Event_Observer $observer
     */
    public function onApplicationCleanCache(Varien_Event_Observer $observer)
    {
        $tags = $observer->getEvent()->getTags();
        if (empty($tags)) {
            $this->_helper->clearCache();
        }
    }

    /**
     * Enable FPC when asked
     *
     * @param Varien_Event_Observer $observer
     */
    public function onMassCacheEnable(Varien_Event_Observer $observer)
    {
        $controller = $observer->getEvent()->getControllerAction();
        $types = $controller->getRequest()->getParam('types');
        if (in_array('bubble_fpc', $types)) {
            $this->_helper->enableCache();
        }
    }

    /**
     * Disable FPC when asked
     *
     * @param Varien_Event_Observer $observer
     */
    public function onMassCacheDisable(Varien_Event_Observer $observer)
    {
        $controller = $observer->getEvent()->getControllerAction();
        $types = $controller->getRequest()->getParam('types');
        if (in_array('bubble_fpc', $types)) {
            $this->_helper->disableCache();
        }
    }

    /**
     * Refresh FPC if asked
     *
     * @param Varien_Event_Observer $observer
     */
    public function onMassCacheRefresh(Varien_Event_Observer $observer)
    {
        $controller = $observer->getEvent()->getControllerAction();
        $types = $controller->getRequest()->getParam('types');
        if (in_array('bubble_fpc', $types)) {
            $this->_helper->clearCache();
        }
    }

    /**
     * Clear some FPC pages when a product is saved
     *
     * @param Varien_Event_Observer $observer
     */
    public function onProductSaveAfter(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product && $this->_enabled) {
            $this->_helper->clearProduct($product);
        }
    }

    /**
     * Clear some FPC pages when a category is saved
     *
     * @param Varien_Event_Observer $observer
     */
    public function onCategorySaveAfter(Varien_Event_Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();
        if ($category && $this->_enabled) {
            $this->_helper->clearCategory($category);
        }
    }

    /**
     * Clear some FPC pages when a page is saved
     *
     * @param Varien_Event_Observer $observer
     */
    public function onPageSaveAfter(Varien_Event_Observer $observer)
    {
        $page = $observer->getEvent()->getObject();
        if ($page && $this->_enabled) {
            $this->_helper->clearPage($page);
        }
    }

    /**
     * Clear some FPC pages when a stock item is saved
     *
     * @param Varien_Event_Observer $observer
     */
    public function onStockItemSaveAfter(Varien_Event_Observer $observer)
    {
        $stockItem = $observer->getEvent()->getItem();
        if ($stockItem && $this->_enabled) {
            $product = Mage::getModel('catalog/product')->load($stockItem->getProductId());
            if ($product->getId()) {
                $this->_helper->clearProduct($product);
            }
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function onControllerRedirect(Varien_Event_Observer $observer)
    {
        $request = Mage::app()->getRequest();
        $fullActionName = sprintf(
            '%s/%s/%s',
            $request->getRequestedRouteName(),
            $request->getRequestedControllerName(),
            $request->getRequestedActionName()
        );
        if ($fullActionName == 'review/product/post') {
            $this->prepareMessages(true);
        }
    }

    /**
     * Clear some FPC pages after prices reindexation
     */
    public function onReindexPriceAfter()
    {
        if ($this->_enabled) {
            $process = Mage::getModel('index/process')->load('catalog_product_price', 'indexer_code');
            if (!$process->getId() || $process->getMode() == Mage_Index_Model_Process::MODE_MANUAL) {
                $this->_helper->clearCache();
            }
            $this->_helper->clearHomeCache();
        }
    }

    /**
     * Clear some FPC pages after URLs reindexation
     */
    public function onReindexUrlAfter()
    {
        if ($this->_enabled) {
            $process = Mage::getModel('index/process')->load('catalog_url', 'indexer_code');
            if (!$process->getId() || $process->getMode() == Mage_Index_Model_Process::MODE_MANUAL) {
                $this->_helper->clearCache();
            }
        }
    }

    /**
     * Clear some FPC pages after catalog search reindexation
     */
    public function onReindexSearchAfter()
    {
        if ($this->_enabled) {
            $process = Mage::getModel('index/process')->load('catalogsearch_fulltext', 'indexer_code');
            if (!$process->getId() || $process->getMode() == Mage_Index_Model_Process::MODE_MANUAL) {
                $this->_helper->clearCache();
            }
        }
    }

    /**
     * Clear some FPC pages after stock reindexation
     */
    public function onReindexStockAfter()
    {
        if ($this->_enabled) {
            $process = Mage::getModel('index/process')->load('cataloginventory_stock', 'indexer_code');
            if (!$process->getId() || $process->getMode() == Mage_Index_Model_Process::MODE_MANUAL) {
                $this->_helper->clearCache();
            }
            $this->_helper->clearHomeCache();
        }
    }

    /**
     * Fix for Magento 1.8.1.0 and form key validation on some actions.
     *
     * @param Varien_Event_Observer $observer
     */
    public function validateFormKey(Varien_Event_Observer $observer)
    {
        /** @var $action Mage_Core_Controller_Varien_Action */
        $action = $observer->getEvent()->getControllerAction();
        if ($action) {
            $formKey = Mage::getSingleton('core/session')->getFormKey();
            $action->getRequest()->setParam('form_key', $formKey);
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function onProductClearFpc(Varien_Event_Observer $observer)
    {
        if (!$this->_enabled || $this->_helper->isSoftCacheClearing()) {
            return;
        }

        /** @var $container Bubble_FullPageCache_Model_Url_Container */
        $container = Mage::getSingleton('bubble_fpc/url_container');

        /** @var $product Mage_Catalog_Model_Product */
        $product = $observer->getEvent()->getProduct();

        // Product urls
        foreach (Mage::app()->getStores() as $store) {
            if ($store->getIsActive()) {
                $urls = $this->_helper->getProductUrls($product, $store);
                $baseUrl = $store->getBaseUrl();
                foreach ($urls as $url) {
                    $url = rtrim($baseUrl . $url->getRequestPath(), '/');
                    $container->offsetSet($url, $url);
                }
            }
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function onCategoryClearFpc(Varien_Event_Observer $observer)
    {
        if (!$this->_enabled || $this->_helper->isSoftCacheClearing()) {
            return;
        }

        /** @var $container Bubble_FullPageCache_Model_Url_Container */
        $container = Mage::getSingleton('bubble_fpc/url_container');

        /** @var $product Mage_Catalog_Model_Category */
        $category = $observer->getEvent()->getCategory();

        foreach (Mage::app()->getStores() as $store) {
            if ($store->getIsActive()) {
                $urls = $this->_helper->getCategoryUrls($category, $store);
                $baseUrl = $store->getBaseUrl();
                foreach ($urls as $url) {
                    $url = rtrim($baseUrl . $url->getRequestPath(), '/');
                    $container->offsetSet($url, $url);
                }
            }
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function onPageClearFpc(Varien_Event_Observer $observer)
    {
        if (!$this->_enabled || $this->_helper->isSoftCacheClearing()) {
            return;
        }

        /** @var $container Bubble_FullPageCache_Model_Url_Container */
        $container = Mage::getSingleton('bubble_fpc/url_container');

        /** @var $product Mage_Cms_Model_Page */
        $page = $observer->getEvent()->getPage();
        foreach (Mage::app()->getStores() as $store) {
            if ($store->getIsActive()) {
                $identifier = $page->getIdentifier();
                $baseUrl = $store->getBaseUrl();
                $url = rtrim($baseUrl . $identifier, '/');
                $container->offsetSet($url, $url);
            }
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function onAdminBlockHtmlBefore(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        $url = false;
        $child = false;
        if ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Edit) {
            $product = $block->getProduct();
            if ($product && $product->getId()) {
                // Add a clear fpc button on product form
                $url = $block->getUrl('*/fpc/clearProduct', array('_current' => true));
                $child = $block->getChild('delete_button');
            }
        } else if ($block instanceof Mage_Adminhtml_Block_Catalog_Category_Edit_Form) {
            $category = $block->getCategory();
            if ($category && $category->getId()) {
                // Add a clear fpc button on category form
                $url = $block->getUrl('*/fpc/clearCategory', array('_current' => true));
                $child = $block->getChild('delete_button');
            }
        } else if ($block instanceof Mage_Adminhtml_Block_Cms_Page_Edit) {
            $page = Mage::registry('cms_page');
            if ($page && $page->getId()) {
                // Add a clear fpc button on page form
                $url = $block->getUrl('*/fpc/clearPage', array('_current' => true));
                $block->addButton('clear_fpc_button', array(
                    'label'     => Mage::helper('bubble_fpc')->__('Clear Full Page Cache'),
                    'onclick'   => "setLocation('$url')",
                    'class'     => 'delete',
                ), 0, -1);
            }
        }

        if ($url && $child) {
            $button = $block->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('bubble_fpc')->__('Clear Full Page Cache'),
                    'onclick'   => "setLocation('$url')",
                    'class'     => 'delete',
                ));
            $child->setBeforeHtml($button->toHtml());
        }
    }

    /**
     * Sync FPC config with Magento config
     *
     * @param Varien_Event_Observer $observer
     */
    public function onConfigSectionSaveAfter(Varien_Event_Observer $observer)
    {
        $section = $observer->getEvent()->getSection();
        if ($section == 'design') {
            Mage::app()->reinitStores();
            $this->_helper->saveDesignExceptions();
        } else if ($section == 'bubble_fpc') {
            Mage::app()->reinitStores();
            $this->_helper->saveGeneralSettings();
            $this->_helper->saveStorageSettings();
            // Clear cache because zip flag and/or backend storage may have changed
            $this->_helper->clearCache();
        }
    }

    /**
     * Refresh welcome cookie after customer login
     *
     * @param Varien_Event_Observer $observer
     */
    public function onCustomerLogin(Varien_Event_Observer $observer)
    {
        $layout = Mage::app()->getLayout();
        $header = $layout->getBlock('header');
        if ($header) {
            $block = $layout->createBlock('page/html_welcome');
            if ($block) {
                $this->cookify('welcome', $block->toHtml());
            }
        }
    }
}