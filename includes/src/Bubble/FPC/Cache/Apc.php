<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FPC_Cache_Apc extends Bubble_FPC_Cache
{
    protected function _init()
    {
        if (!extension_loaded('apc')) {
            throw new Exception('The APC extension must be loaded');
        }
    }

    public function load($id)
    {
        $result = apc_fetch($this->_id($id));
        if ($result) {
            return $this->_unzip($result);
        }

        return $result;
    }

    public function save($id, $data, $lifetime = null)
    {
        if (null === $lifetime) {
            $lifetime = $this->getLifetime();
        }

        $result = apc_store($this->_id($id), $this->_zip($data), $lifetime);

        return $result;
    }

    public function delete($id)
    {
        return apc_delete($this->_id($id));
    }

    public function deletePath($path, $prefix = '', $suffix = '')
    {
        $info = apc_cache_info('user');
        if (isset($info['cache_list'])) {
            $delimiter = '#';
            foreach ($info['cache_list'] as $cache) {
                $key = $cache['info'];
                $pattern = sprintf(
                    '%s^%s.*%s.*%s$%si',
                    $delimiter,
                    preg_quote($prefix, $delimiter),
                    preg_quote(FPC_DS . $path, $delimiter),
                    preg_quote($suffix, $delimiter),
                    $delimiter
                );
                if (preg_match($pattern, $key)) {
                    $this->delete($key);
                }
            }
        }

        return $this;
    }

    public function flush($ns)
    {
        return apc_clear_cache('user');
    }

    public function test()
    {
        return (bool) ini_get('apc.enabled');
    }
}