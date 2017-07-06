<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FPC
{
    /**
     * FPC config from JSON file
     *
     * @var array
     */
    protected static $_config;

    /**
     * Cache object
     *
     * @var array
     */
    protected static $_cache;

    /**
     * Current store
     *
     * @var string
     */
    protected $_store = FPC_DEFAULT_STORE;

    /**
     * @var bool
     */
    protected $_clear = false;

    /**
     * Initialization
     */
    public function __construct()
    {
        if (isset($_COOKIE['store'])) {
            $this->_store = $_COOKIE['store'];
        } elseif (isset($_SERVER['MAGE_RUN_CODE']) && isset($_SERVER['MAGE_RUN_TYPE']) && $_SERVER['MAGE_RUN_TYPE'] == 'store') {
            $this->_store = $_SERVER['MAGE_RUN_CODE'];
        }

        if (isset($_SERVER['QUERY_STRING'])) {
            $params = array();
            parse_str($_SERVER['QUERY_STRING'], $params);
            if (isset($params['fpc']) && $params['fpc'] === '0') {
                $this->_clear = true; // will force cached page to be cleared if hit
                unset($params['fpc']);
                $_SERVER['QUERY_STRING'] = http_build_query($params);
            }
        }
    }

    /**
     * Can we use FPC for current request?
     *
     * @return bool
     */
    public function canRun()
    {
        return defined('FPC_ENABLED')
            && FPC_ENABLED
            && self::isRequestCacheable()
            && (!self::isAjax() || self::isAjaxEnabled($this->_store))
            && self::isEnabled()
            && self::isStoreEnabled($this->_store);
    }

    /**
     * Check if a cached file can be used for current request
     */
    public function run()
    {
        $layoutDirs = array();
        if (isset($_COOKIE['layout']) && strlen($_COOKIE['layout'])) {
            $layoutDirs = str_split($_COOKIE['layout'], 1);
        }
        $layoutDirs = array_merge($layoutDirs, self::getRequestCacheDirs($this->_store));
        array_unshift($layoutDirs, $this->_store);
        $cacheId = implode(FPC_DS, $layoutDirs) . FPC_DS . self::getRequestCacheFile();
        $data = self::getCache($this->_store)->load($cacheId);
        if (!empty($data)) {
            if ($this->_clear) {
                self::getCache($this->_store)->delete($cacheId); // force cache removal
            } else {
                ob_end_clean();
                if (self::isJson($data)) {
                    header('Content-Type: application/json');
                } else {
                    header('Content-Type: text/html; charset=UTF-8');
                }
                header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
                header('Pragma: no-cache');
                header('Bubble-FPC: ' . self::getVersion());
                echo $data;
                exit;
            }
        }
    }

    /**
     * @param $str
     * @return bool
     */
    static public function isJson($str)
    {
        @json_decode($str);

        return json_last_error() == JSON_ERROR_NONE;
    }

    /**
     * @return bool
     */
    static public function isRequestCacheable()
    {
        return empty($_POST)
            && empty($_FILES)
            && isset($_SERVER['HTTP_HOST'])
            && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET'
            && (!isset($_COOKIE['fpc']) || $_COOKIE['fpc']);
    }

    /**
     * @param $value
     * @param int $length
     * @return string
     */
    static public function crypt($value, $length = null)
    {
        if (!$length) {
            $length = FPC_DEFAULT_HASH_LENGTH;
        }
        if (!is_scalar($value)) {
            $value = serialize($value);
        }

        return substr(sha1($value), 0, $length);
    }

    /**
     * @return string
     */
    static public function getRequestHash()
    {
        return self::crypt(trim($_SERVER['REQUEST_URI'], '/'), FPC_DEFAULT_HASH_LENGTH);
    }

    /**
     * @return string
     */
    static public function getRequestUri()
    {
        return trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    }

    /**
     * @return array
     */
    static public function getRequestDirs()
    {
        $dirs = explode('/', self::getRequestUri());
        array_pop($dirs); // remove last element which is used for cache filename

        return $dirs;
    }

    /**
     * @param string $store
     * @return array
     */
    static public function getRequestCacheDirs($store)
    {
        $dirs = array_merge(str_split(self::getRequestHash(), 1), self::getRequestDirs());
        $designHash = self::getDesignHash($store);
        if (!empty($designHash)) {
            $dirs = array_merge(str_split($designHash, 1), $dirs);
        }
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off') {
            array_unshift($dirs, FPC_SECURE_DIR);
        }
        if (self::isAjax()) {
            array_unshift($dirs, 'ajax');
        }
        array_unshift($dirs, $_SERVER['HTTP_HOST']);

        return $dirs;
    }

    /**
     * @return string
     */
    static public function getRequestCacheFile()
    {
        $file = basename(self::getRequestUri());
        if (!$file) {
            $file = FPC_HOME_FILENAME;
        }
        $file = pathinfo($file, PATHINFO_FILENAME);
        $query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
        if ($query) {
            $file .= urlencode('?' . $query);
        }
        $file .= FPC_FILE_EXTENSION;

        return $file;
    }

    /**
     * @return string
     */
    static public function getVersion()
    {
        preg_match('#@version\s+(\d+\.\d+\.\d+)#', file_get_contents(__FILE__), $matches);

        return $matches[1];
    }

    /**
     * @return string
     */
    static public function getConfigFile()
    {
        return FPC_DIR . FPC_DS . FPC_CONFIG_FILE;
    }

    /**
     * @return stdClass
     */
    static public function getConfig()
    {
        if (null === self::$_config) {
            self::$_config = new stdClass(); // object to keep reference on it
            $configFile = self::getConfigFile();
            if (file_exists($configFile) && is_file($configFile) && filesize($configFile)) {
                self::$_config = @json_decode(file_get_contents($configFile));
            }
        }

        return self::$_config;
    }

    /**
     * @param string $store
     * @return Bubble_FPC_Cache
     */
    static public function getCache($store)
    {
        if (null === self::$_cache) {
            $settings = self::getStorageSettings($store);
            $options = $settings ? (array) $settings : array();
            $options['lifetime'] = self::getLifetime($store);
            $options['zip'] = self::getEnableCompression($store);
            self::$_cache = Bubble_FPC_Cache::factory($options);
        }

        return self::$_cache;
    }

    /**
     * @param stdClass $config
     * @return int
     */
    static public function saveConfig(stdClass $config)
    {
        // Pretty output is PHP 5.4+
        $options = defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0;

        return @file_put_contents(self::getConfigFile(), json_encode($config, $options));
    }

    /**
     * @return bool
     */
    static public function isEnabled()
    {
        $config = self::getConfig();

        return isset($config->enabled) ? (bool) $config->enabled : true;
    }

    /**
     * @param string $store
     * @return bool
     */
    static public function isStoreEnabled($store)
    {
        $config = self::getConfig();

        return isset($config->$store) && isset($config->$store->enabled) ?
            (bool) $config->$store->enabled :
            true;
    }

    /**
     * @return int
     */
    static public function enable()
    {
        $config = self::getConfig();
        $config->enabled = true;

        return self::saveConfig($config);
    }

    /**
     * @return int
     */
    static public function disable()
    {
        $config = self::getConfig();
        $config->enabled = false;

        return self::saveConfig($config);
    }

    /**
     * @param string $store
     * @return int
     */
    static public function enableStore($store)
    {
        $config = self::getConfig();
        if (!isset($config->$store)) {
            $config->$store = new stdClass();
        }
        $config->$store->enabled = true;

        return self::saveConfig($config);
    }

    /**
     * @param string $store
     * @return int
     */
    static public function disableStore($store)
    {
        $config = self::getConfig();
        if (!isset($config->$store)) {
            $config->$store = new stdClass();
        }
        $config->$store->enabled = false;

        return self::saveConfig($config);
    }

    /**
     * @return bool
     */
    static public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * @param $store
     * @return bool
     */
    static public function isAjaxEnabled($store)
    {
        return self::getEnableAjax($store);
    }

    /**
     * @param string $store
     * @return bool
     */
    static public function getEnableAjax($store)
    {
        $config = self::getConfig();

        return isset($config->$store) && isset($config->$store->enable_ajax) ?
            $config->$store->enable_ajax :
            false;
    }

    /**
     * @param string $store
     * @param bool $bool
     * @return int
     */
    static public function setEnableAjax($store, $bool)
    {
        $config = self::getConfig();
        if (!isset($config->$store)) {
            $config->$store = new stdClass();
        }
        $config->$store->enable_ajax = (bool) $bool;

        return self::saveConfig($config);
    }

    /**
     * @param string $store
     * @return bool|int
     */
    static public function getLifetime($store)
    {
        $config = self::getConfig();

        return isset($config->$store) && isset($config->$store->cache_lifetime) ?
            $config->$store->cache_lifetime :
            false;
    }

    /**
     * @param string $store
     * @param int $lifetime
     * @return int
     */
    static public function setLifetime($store, $lifetime)
    {
        $config = self::getConfig();
        $lifetime = abs((int) $lifetime);
        if (!isset($config->$store)) {
            $config->$store = new stdClass();
        }
        $config->$store->cache_lifetime = $lifetime > 0 ? $lifetime : false;

        return self::saveConfig($config);
    }

    /**
     * @param string $store
     * @return bool|int
     */
    static public function getEnableCompression($store)
    {
        $config = self::getConfig();

        return isset($config->$store) && isset($config->$store->zip) ?
            $config->$store->zip :
            false;
    }

    /**
     * @param string $store
     * @param bool $bool
     * @return int
     */
    static public function setEnableCompression($store, $bool)
    {
        $config = self::getConfig();
        if (!isset($config->$store)) {
            $config->$store = new stdClass();
        }
        $config->$store->zip = (bool) $bool;

        return self::saveConfig($config);
    }

    /**
     * @param string $store
     * @return bool|array
     */
    static public function getStorageSettings($store)
    {
        $config = self::getConfig();

        return isset($config->$store) && isset($config->$store->storage) ?
            $config->$store->storage :
            false;
    }

    /**
     * @param string $store
     * @param array $settings
     * @return int
     */
    static public function setStorageSettings($store, array $settings)
    {
        $config = self::getConfig();
        if (!isset($config->$store)) {
            $config->$store = new stdClass();
        }
        $config->$store->storage = $settings;

        return self::saveConfig($config);
    }

    /**
     * @param string $store
     * @return array
     */
    static public function getDesignHash($store)
    {
        $design = array();
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $exceptions = self::getDesignExceptions($store);
            if (!empty($exceptions)) {
                foreach ($exceptions as $key => $rules) {
                    foreach ($rules as $rule) {
                        $regexp = '/' . trim($rule->regexp, '/') . '/';
                        if (@preg_match($regexp, $_SERVER['HTTP_USER_AGENT'])) {
                            $design[$key] = $rule->value;
                        }
                    }
                }
            }
        }

        return !empty($design) ? self::crypt($design) : '';
    }

    /**
     * @param string $store
     * @return array
     */
    static public function getDesignExceptions($store)
    {
        $config = self::getConfig();

        return isset($config->$store) && isset($config->$store->design_exceptions) ?
            $config->$store->design_exceptions :
            array();
    }

    /**
     * @param $store
     * @param array $exceptions
     * @return int
     */
    static public function setDesignExceptions($store, array $exceptions)
    {
        $config = self::getConfig();
        if (!isset($config->$store)) {
            $config->$store = new stdClass();
        }
        $config->$store->design_exceptions = $exceptions;

        return self::saveConfig($config);
    }
}