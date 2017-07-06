<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FullPageCache_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Cacheable actions.
     *
     * @var array
     */
    protected $_actions;

    /**
     * AJAX blocks.
     *
     * @var array
     */
    protected $_ajaxBlocks;

    /**
     * Cookify this blocks.
     *
     * @var array
     */
    protected $_cookieBlocks = array(
        'global_messages',
        'welcome',
    );

    /**
     * List of cleared items.
     *
     * @var array
     */
    protected $_clearedItems = array();

    /**
     * @param Varien_Object $product
     * @return $this
     */
    public function clearProductCache(Varien_Object $product)
    {
        if ($product->getId()) {
            // Block HTML
            $product->cleanCache();

            // Product urls
            foreach (Mage::app()->getStores() as $store) {
                $urls = $this->getProductUrls($product, $store);
                foreach ($urls as $url) {
                    $this->clearCacheItem($url->getRequestPath(), $store);
                }
            }

            Mage::dispatchEvent('bubble_fpc_clear_cache_product_after', array('product' => $product));
        }

        return $this;
    }

    /**
     * @param Varien_Object $category
     * @return $this
     */
    public function clearCategoryCache(Varien_Object $category)
    {
        if ($category->getId()) {
            // Block HTML
            Mage::app()->cleanCache('catalog_category_' . $category->getId());

            // Category urls
            foreach (Mage::app()->getStores() as $store) {
                $urls = $this->getCategoryUrls($category, $store);
                foreach ($urls as $url) {
                    $this->clearCacheItem($url->getRequestPath(), $store);
                }
            }

            Mage::dispatchEvent('bubble_fpc_clear_cache_category_after', array('category' => $category));
        }

        return $this;
    }

    /**
     * @param Varien_Object $page
     * @return $this
     */
    public function clearPageCache(Varien_Object $page)
    {
        if ($page->getId()) {
            foreach (Mage::app()->getStores() as $store) {
                $identifier = $page->getIdentifier();
                if (empty($identifier)) {
                    $this->clearCacheItem(FPC_HOME_FILENAME, $store);
                } else {
                    $this->clearCacheItem($identifier, $store);
                }
            }

            Mage::dispatchEvent('bubble_fpc_clear_cache_page_after', array('page' => $page));
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function clearHomeCache()
    {
        foreach (Mage::app()->getStores() as $store) {
            $this->clearCacheItem(FPC_HOME_FILENAME, $store);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function clearSitemapCache()
    {
        foreach (Mage::app()->getStores() as $store) {
            $this->clearCacheItem('catalog/seo_sitemap/product', $store);
            $this->clearCacheItem('catalog/seo_sitemap/category', $store);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function clearSearchCache()
    {
        foreach (Mage::app()->getStores() as $store) {
            $this->clearCacheItem('catalogsearch/advanced/index', $store);
            $this->clearCacheItem('catalogsearch/advanced', $store);
            $this->clearCacheItem('catalogsearch/result/index', $store);
            $this->clearCacheItem('catalogsearch/result', $store);
        }

        return $this;
    }

    /**
     * @param $path
     * @param $store
     * @return $this
     */
    public function clearCacheItem($path, $store)
    {
        try {
            $key = md5(serialize(func_get_args()));
            if (in_array($key, $this->_clearedItems)) {
                return $this; // Already done, skipping
            }
            $this->_clearedItems[] = $key;
            $baseDir = $this->getStoreDir($store);
            $cache = $this->getCacheInstance($store);
            $pathinfo = pathinfo($path);
            $path = '';
            if ($pathinfo['dirname'] != '.') {
                $path = implode(DS, explode('/', $pathinfo['dirname'])) . DS;
            }
            $path .= $pathinfo['filename'];

            $cache->deletePath($path, $baseDir, FPC_FILE_EXTENSION);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isFpcEnabled()
    {
        return defined('FPC_ENABLED') && Mage::app()->useCache('bubble_fpc');
    }

    /**
     * @return bool
     */
    public function isCacheable()
    {
        return $this->isFpcEnabled()
            && class_exists('Bubble_FPC')
            && Bubble_FPC::isRequestCacheable();
    }

    /**
     * @param $store
     * @return bool
     */
    public function isStoreCacheable($store = null)
    {
        return $this->isFpcEnabled()
            && class_exists('Bubble_FPC')
            && Bubble_FPC::isStoreEnabled(Mage::app()->getStore($store)->getCode());
    }

    /**
     * @return bool
     */
    public function enableCache()
    {
        return class_exists('Bubble_FPC') && Bubble_FPC::enable();
    }

    /**
     * @return bool
     */
    public function disableCache()
    {
        return class_exists('Bubble_FPC') && Bubble_FPC::disable();
    }

    /**
     * @param $store
     * @return bool
     */
    public function enableStoreCache($store)
    {
        return class_exists('Bubble_FPC') && Bubble_FPC::enableStore(Mage::app()->getStore($store)->getCode());
    }

    /**
     * @param $store
     * @return int
     */
    public function disableStoreCache($store)
    {
        return class_exists('Bubble_FPC') && Bubble_FPC::disableStore(Mage::app()->getStore($store)->getCode());
    }

    /**
     * @param null $store
     * @return string
     */
    public function getStoreDir($store = null)
    {
        return Mage::app()->getStore($store)->getCode();
    }

    /**
     * @return Mage_Core_Controller_Request_Http
     */
    public function getRequest()
    {
        return Mage::app()->getRequest();
    }

    /**
     * @param string $delimiter
     * @return string
     */
    public function getFullActionName($delimiter = '/')
    {
        /* @var $request Mage_Core_Controller_Request_Http */
        $request = $this->getRequest();

        return $request->getRequestedRouteName() . $delimiter .
            $request->getRequestedControllerName() . $delimiter .
            $request->getRequestedActionName();
    }

    /**
     * @return array
     */
    public function getCacheableActions()
    {
        if (null === $this->_actions) {
            $this->_actions = array();
            $collection = Mage::getModel('bubble_fpc/action')->getCollection()
                ->addFieldToFilter('is_active', 1);
            foreach ($collection as $action) {
                $this->_actions[] = $action->getName();
            }
        }

        $actions = new Varien_Object($this->_actions);
        Mage::dispatchEvent('bubble_fpc_cacheable_actions', array('actions' => $actions));

        return $actions->getData();
    }

    /**
     * @return array
     */
    public function getAjaxBlocks()
    {
        if (null === $this->_ajaxBlocks) {
            $this->_ajaxBlocks = array();
            $collection = Mage::getModel('bubble_fpc/block')->getCollection()
                ->addFieldToFilter('is_active', 1);
            foreach ($collection as $block) {
                $this->_ajaxBlocks[] = $block->getName();
            }
        }

        $blocks = new Varien_Object($this->_ajaxBlocks);
        Mage::dispatchEvent('bubble_fpc_ajax_blocks', array('blocks' => $blocks));

        return $blocks->getData();
    }

    /**
     * @return array
     */
    public function getCookieBlocks()
    {
        $blocks = new Varien_Object($this->_cookieBlocks);
        Mage::dispatchEvent('bubble_fpc_cookie_blocks', array('blocks' => $blocks));

        return $blocks->getData();
    }

    /**
     * @param bool $fullActionName
     * @return bool
     */
    public function isActionCacheable($fullActionName = false)
    {
        if (false === $fullActionName) {
            $fullActionName = $this->getFullActionName();
        }
        $cacheableActions = $this->getCacheableActions();

        return in_array($fullActionName, $cacheableActions);
    }

    /**
     * @param $html
     * @param $store
     * @return $this
     */
    public function saveLayout($html, $store = null)
    {
        if (!$html) {
            return $this;
        }

        try {
            $layoutDirs = array();
            $layoutHash = $this->getLayoutHash();
            if (strlen($layoutHash)) {
                $layoutDirs = str_split($layoutHash, 1);
            }
            $store = Mage::app()->getStore($store);
            $layoutDirs = array_merge($layoutDirs, Bubble_FPC::getRequestCacheDirs($store->getCode()));
            array_unshift($layoutDirs, $store->getCode());
            $cacheId = implode(FPC_DS, $layoutDirs) . FPC_DS . Bubble_FPC::getRequestCacheFile();
            $this->getCacheInstance($store)->save($cacheId, trim($html));
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getLayoutHash()
    {
        $params = Mage::getModel('bubble_fpc/layout')->getParams();

        return !empty($params) ? $this->crypt($params) : '';
    }

    /**
     * @return string
     */
    public function updateCookieLayout()
    {
        // Will be deleted if hash is empty, which is good
        $this->cookie()->set('layout', $this->getLayoutHash());

        return $this;
    }

    /**
     * @param null $store
     * @return $this
     */
    public function updateCookieStore($store = null)
    {
        $cookie = $this->cookie();
        $store = Mage::app()->getStore($store);
        if (!$cookie->get('store') || $cookie->get('store') != $store->getCode()) {
            // Save current store to ensure that it will be used by fpc.php
            $cookie->set('store', $store->getCode(), true);

        }

        return $this;
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function clearCache($storeId = null)
    {
        foreach (Mage::app()->getStores() as $store) {
            if ($storeId && $storeId != $store->getId()) {
                continue;
            }
            $this->getCacheInstance($store)->flush($store->getCode());
        }

        return $this;
    }

    /**
     * @return Mage_Core_Model_Cache
     */
    public function invalidateCache()
    {
        return Mage::app()->getCacheInstance()->invalidateType('bubble_fpc');
    }

    /**
     * @param $value
     * @param $length
     * @return string
     */
    public function crypt($value, $length = null)
    {
        return class_exists('Bubble_FPC') ? Bubble_FPC::crypt($value, $length) : '';
    }

    /**
     * @param $html
     * @return mixed
     */
    public function cleanHtml($html)
    {
        $html = Mage::getSingleton('core/url')->sessionUrlVar($html);

        return $html;
    }

    /**
     * @param null $store
     * @return array
     */
    public function getStoreUrls($store = null)
    {
        $store = Mage::app()->getStore($store);

        // Categories
        $model = Mage::getResourceModel('sitemap/catalog_category');
        if (!$model) {
            $model = new Mage_Sitemap_Model_Resource_Catalog_Category();
        }
        $categories = $model->getCollection($store->getId());

        // Products
        $model = Mage::getResourceModel('sitemap/catalog_product');
        if (!$model) {
            $model = new Mage_Sitemap_Model_Resource_Catalog_Product();
        }
        $products = $model->getCollection($store->getId());

        // CMS Pages
        $model = Mage::getResourceModel('sitemap/cms_page');
        if (!$model) {
            $model = new Mage_Sitemap_Model_Resource_Cms_Page();
        }
        $pages = $model->getCollection($store->getId());

        $objects = array_merge($categories, $products, $pages);
        $urls = array();
        $baseUrl = $store->getBaseUrl();
        foreach ($objects as $object) {
            $urls[] = $baseUrl . $object->getUrl();
        }
        $urls = array_unique($urls);
        sort($urls);

        return $urls;
    }

    /**
     * @return Mage_Core_Model_Cookie
     */
    public function cookie()
    {
        return Mage::getSingleton('core/cookie');
    }

    /**
     * @return Mage_Customer_Model_Session
     */
    public function session()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * @param Varien_Object $product
     * @param null $store
     * @return Mage_Core_Model_Resource_Url_Rewrite_Collection
     */
    public function getProductUrls(Varien_Object $product, $store = null)
    {
        $urls = Mage::getModel('core/url_rewrite')->getCollection()
            ->addFieldToFilter('product_id', $product->getId())
            ->addFieldToFilter('options', array('null' => true));

        // Do not include product urls with category path if not needed
        if (!Mage::getStoreConfigFlag(Mage_Catalog_Helper_Product::XML_PATH_PRODUCT_URL_USE_CATEGORY, $store)) {
            $urls->addFieldToFilter('category_id', array('null' => true));
        }

        if ($store) {
            $urls->addStoreFilter($store, false);
        }

        return $urls;
    }

    /**
     * @param Varien_Object $category
     * @param null $store
     * @return Mage_Core_Model_Resource_Url_Rewrite_Collection
     */
    public function getCategoryUrls(Varien_Object $category, $store = null)
    {
        $urls = Mage::getModel('core/url_rewrite')->getCollection()
            ->addFieldToFilter('category_id', $category->getId())
            ->addFieldToFilter('product_id', array('null' => true))
            ->addFieldToFilter('options', array('null' => true));

        if ($store) {
            $urls->addStoreFilter($store, false);
        }

        return $urls;
    }

    /**
     * @param Varien_Object $product
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getProductCategoryCollection(Varien_Object $product)
    {
        $resource = Mage::getResourceModel('catalog/category_indexer_product');
        $conn = $resource->getReadConnection();
        $select = $conn->select()
            ->distinct(true)
            ->from($resource->getMainTable(), array('category_id'))
            ->where('product_id = :product_id');
        $categoryIds = $conn->fetchCol($select, array('product_id' => $product->getId()));

        $collection = Mage::getModel('catalog/category')->getCollection()
            ->addIdFilter($categoryIds);

        return $collection;
    }

    /**
     * @return bool
     */
    public function isSoftCacheClearing()
    {
        return Mage::getStoreConfigFlag('bubble_fpc/general/enable_soft_cache_clearing');
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param bool $force
     * @return $this
     */
    public function clearProduct(Mage_Catalog_Model_Product $product, $force = false)
    {
        if ($this->isSoftCacheClearing() && !$force) {
            $this->invalidateCache();
        } else {
            $this->clearProductCache($product);
            $this->clearLinkedProducts($product);
            $this->clearProductCategories($product);
            $this->clearSitemapCache();
            $this->clearSearchCache();
        }

        return $this;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return $this
     */
    public function clearLinkedProducts(Mage_Catalog_Model_Product $product)
    {
        // Clear linked products
        $links = Mage::getModel('catalog/product_link')
            ->getLinkCollection()
            ->addFieldToFilter('linked_product_id', $product->getId());
        $productIds = array_unique($links->walk('getProductId'));
        if (!empty($productIds)) {
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addIdFilter($productIds);
            foreach ($collection as $product) {
                $this->clearProductCache($product);
            }
        }

        return $this;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return $this
     */
    public function clearProductCategories(Mage_Catalog_Model_Product $product)
    {
        // Clear product categories
        $collection = $this->getProductCategoryCollection($product);
        foreach ($collection as $category) {
            $this->clearCategoryCache($category);
        }

        return $this;
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     * @param bool $force
     * @return $this
     */
    public function clearCategory(Mage_Catalog_Model_Category $category, $force = false)
    {
        if ($this->isSoftCacheClearing() && !$force) {
            $this->invalidateCache();
        } else {
            $this->clearCategoryCache($category);
            $this->clearSitemapCache();
            $this->clearSearchCache();
        }

        return $this;
    }

    /**
     * @param Mage_Cms_Model_Page $page
     * @param bool $force
     * @return $this
     */
    public function clearPage(Mage_Cms_Model_Page $page, $force = false)
    {
        if ($this->isSoftCacheClearing() && !$force) {
            $this->invalidateCache();
        } else {
            $this->clearPageCache($page);
        }

        return $this;
    }

    /**
     * @param $url
     * @param null $store
     * @return array
     */
    public function call($url, $store = null)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Bubble FullPageCache Agent https://www.bubbleshop.net');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $cookies = array();
        $sessionId = Mage::getSingleton('core/session')->getCurlSessionId();
        if ($sessionId) {
            $cookies['frontend'] = $sessionId;
        }
        if ($store) {
            $cookies['store'] = $store;
        }
        if (!empty($cookies)) {
            curl_setopt($ch, CURLOPT_COOKIE, http_build_query($cookies, null, '; '));
        }
        if (substr($url, 0, 5) === 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $exec = curl_exec($ch);

        if (!curl_errno($ch)) {
            $result = curl_getinfo($ch);
            preg_match('/^Set-Cookie:\s*frontend=([^;]*)/mi', $exec, $m);
            if (isset($m[1])) {
                Mage::getSingleton('core/session')->setCurlSessionId($m[1]);
            }
        } else {
            $result = array('error' => curl_error($ch));
        }

        curl_close($ch);

        return $result;
    }

    /**
     * @return bool
     */
    public function saveGeneralSettings()
    {
        if (class_exists('Bubble_FPC')) {
            foreach (Mage::app()->getStores() as $store) {
                /** @var $store Mage_Core_Model_Store */
                if ($store->getIsActive()) {
                    // Save cache lifetime
                    $lifetime = $store->getConfig('bubble_fpc/general/cache_lifetime');
                    Bubble_FPC::setLifetime($store->getCode(), $lifetime);

                    // Save compression flag
                    $zip = $store->getConfig('bubble_fpc/general/enable_zip');
                    Bubble_FPC::setEnableCompression($store->getCode(), $zip);

                    // Save cache AJAX requests flag
                    $ajax = $store->getConfig('bubble_fpc/general/enable_ajax');
                    Bubble_FPC::setEnableAjax($store->getCode(), $ajax);
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function saveStorageSettings()
    {
        if (class_exists('Bubble_FPC')) {
            foreach (Mage::app()->getStores() as $store) {
                /** @var $store Mage_Core_Model_Store */
                if ($store->getIsActive()) {
                    $settings = $store->getConfig('bubble_fpc/storage');
                    Bubble_FPC::setStorageSettings($store->getCode(), $settings);
                }
            }
        }
    }

    /**
     * @return $this
     */
    public function saveDesignExceptions()
    {
        if (class_exists('Bubble_FPC')) {
            foreach (Mage::app()->getStores() as $store) {
                /** @var $store Mage_Core_Model_Store */
                if ($store->getIsActive()) {
                    $exceptions = array();
                    $paths = array(
                        'package'   => 'design/package/ua_regexp',
                        'template'  => 'design/theme/template_ua_regexp',
                        'skin'      => 'design/theme/skin_ua_regexp',
                        'layout'    => 'design/theme/layout_ua_regexp',
                        'default'   => 'design/theme/default_ua_regexp',
                    );
                    foreach ($paths as $key => $path) {
                        $exceptions[$key] = array();
                        $config = $store->getConfig($path);
                        if ($config) {
                            $regexps = @unserialize($config);
                            if (!empty($regexps)) {
                                $exceptions[$key] = array_values($regexps);
                            }
                        }
                    }

                    Bubble_FPC::setDesignExceptions($store->getCode(), $exceptions);
                }
            }
        }

        return $this;
    }

    /**
     * @param null $store
     * @return Bubble_FPC_Cache
     */
    public function getCacheInstance($store = null)
    {
        return Bubble_FPC::getCache(Mage::app()->getStore($store)->getCode());
    }
}