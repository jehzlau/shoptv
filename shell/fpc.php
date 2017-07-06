<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
require_once 'abstract.php';

class Bubble_Shell_FPC extends Mage_Shell_Abstract
{
    public function run()
    {
        try {
            if ($this->getArg('generate') || $this->getArg('generate-all')) {
                $start = microtime(true);
                $this->_copyright();

                if ($this->getArg('generate')) {
                    // Generate cache for store
                    $this->_generateStoreCache($this->_getStore());
                } else {
                    // Generate cache for all stores
                    foreach (Mage::app()->getStores() as $store) {
                        $this->_generateStoreCache($store);
                    }
                }

                $end = microtime(true);
                $this->_echo(sprintf('Duration: %ss', round($end - $start, 2)));
            } else {
                echo $this->usageHelp();
            }
        } catch (Mage_Core_Model_Store_Exception $e) {
            $this->_fault('Invalid store specified');
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_fault($e->getMessage());
        }
    }

    protected function _generateStoreCache(Varien_Object $store)
    {
        if (!$store->getIsActive()) {
            return $this;
        }

        $this->_echo(sprintf('Generating cache for store %s...', $store->getCode()));

        $urls = Mage::helper('bubble_fpc')->getStoreUrls($store);
        if (empty($urls)) {
            Mage::throwException('No URL found for cache generation');
        }
        $count = count($urls);
        foreach ($urls as $i => $url) {
            $info = Mage::helper('bubble_fpc')->call($url, $store->getCode());
            $this->_echo(sprintf(
                '%s/%s %d %F %s',
                str_pad($i + 1, strlen($count), ' ', STR_PAD_LEFT),
                $count,
                $info['http_code'],
                $info['total_time'],
                $url
            ));
        }

        $this->_echo('');

        return $this;
    }

    protected function _getStore()
    {
        if ($this->getArg('store')) {
            $store = Mage::app()->getStore($this->getArg('store'));
        } else {
            $store = Mage::app()->getDefaultStoreView();
        }

        return $store;
    }

    protected function _copyright()
    {
        $this->_echo('-------------------------------------------');
        $this->_echo('Bubble Full Page Cache v' . self::getVersion() . ' for Magento');
        $this->_echo('(c) ' . date('Y') . ' BubbleShop, by Johann Reinke');
        $this->_echo('https://www.bubbleshop.net');
        $this->_echo('-------------------------------------------');

        return $this;
    }

    protected function _fault($str)
    {
        $this->_echo($str);
        exit;
    }

    protected function _echo($str)
    {
        echo $str . PHP_EOL;

        return $this;
    }

    protected function _validate()
    {
        if (!Mage::isInstalled()) {
            exit('Please install magento before running this script.');
        }

        if (!Mage::helper('core')->isDevAllowed()) {
            exit('You are not allowed to run this script.');
        }

        if (!Mage::helper('core')->isModuleEnabled('Bubble_FullPageCache')) {
            exit('Please enable Bubble_FullPageCache module before running this script.');
        }

        if (!extension_loaded('curl')) {
            exit('This script needs cURL.');
        }

        return true;
    }

    static public function getVersion()
    {
        preg_match('#@version\s+(\d+\.\d+\.\d+)#', file_get_contents(__FILE__), $matches);

        return $matches[1];
    }

    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f shell/fpc.php -- [options]

  -h                Short alias for help
  --generate        Generate cache (if store code is not specified, default store will be used)
  --store <code>    Store to use
  --generate-all    Generate cache for all active stores
  help              This help

USAGE;
    }
}

$shell = new Bubble_Shell_FPC();
$shell->run();
