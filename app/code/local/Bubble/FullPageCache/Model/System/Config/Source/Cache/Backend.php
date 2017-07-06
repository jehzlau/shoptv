<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FullPageCache_Model_System_Config_Source_Cache_Backend
{
    public function toOptionArray()
    {
        $options = array(
            array(
                'value' => 'filesystem',
                'label' => Mage::helper('bubble_fpc')->__('Filesystem'),
            ),
        );

        if (extension_loaded('memcached')) {
            $options[] = array(
                'value' => 'memcached',
                'label' => Mage::helper('bubble_fpc')->__('Memcached (Memcached object)'),
            );
        }

        if (extension_loaded('memcache')) {
            $options[] = array(
                'value' => 'memcache',
                'label' => Mage::helper('bubble_fpc')->__('Memcached (Memcache object)'),
            );
        }

        if (extension_loaded('pdo_mysql')) {
            $options[] = array(
                'value' => 'mysql',
                'label' => Mage::helper('bubble_fpc')->__('MySQL'),
            );
        }

        if (extension_loaded('redis')) {
            $options[] = array(
                'value' => 'redis',
                'label' => Mage::helper('bubble_fpc')->__('Redis'),
            );
        }

        if (extension_loaded('apc') && ini_get('apc.enabled')) {
            $options[] = array(
                'value' => 'apc',
                'label' => Mage::helper('bubble_fpc')->__('APC'),
            );
        }

        return $options;
    }
}