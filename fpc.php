<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
define('FPC_ENABLED', true);
define('FPC_DS', DIRECTORY_SEPARATOR);
define('FPC_BP', dirname(__FILE__) . FPC_DS);
define('FPC_FILE_EXTENSION', '.php');
define('FPC_CONFIG_FILE', 'config.json');
define('FPC_SECURE_DIR', 'https');
define('FPC_DB_CONFIG_FILE', FPC_BP . 'app' . FPC_DS . 'etc' . FPC_DS . 'local.xml');
define('FPC_DB_TABLE_NAME', 'bubble_fpc_cache');
if (!defined('FPC_DEFAULT_HASH_LENGTH')) {
    define('FPC_DEFAULT_HASH_LENGTH', 3);
}
if (!defined('FPC_DIR')) {
    define('FPC_DIR', FPC_BP . 'var' . FPC_DS . 'fpc');
}
if (!is_dir(FPC_DIR)) {
    @mkdir(FPC_DIR, 0777, true);
}
if (!defined('FPC_HOME_FILENAME')) {
    define('FPC_HOME_FILENAME', 'index');
}
if (!defined('FPC_DEFAULT_STORE')) {
    define('FPC_DEFAULT_STORE', 'default');
}

$requires = array(
    array('lib', 'Bubble', 'FPC'),
    array('lib', 'Bubble', 'FPC', 'Cache'),
    array('lib', 'Bubble', 'FPC', 'Cache', 'Apc'),
    array('lib', 'Bubble', 'FPC', 'Cache', 'Filesystem'),
    array('lib', 'Bubble', 'FPC', 'Cache', 'Memcached'),
    array('lib', 'Bubble', 'FPC', 'Cache', 'Memcache'),
    array('lib', 'Bubble', 'FPC', 'Cache', 'Mysql'),
    array('lib', 'Bubble', 'FPC', 'Cache', 'Redis'),
);

foreach ($requires as $require) {
    require FPC_BP . implode(FPC_DS, $require) . '.php';
}

$fpc = new Bubble_FPC();
if ($fpc->canRun()) {
    $fpc->run();
}