<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FPC_Cache_Memcached extends Bubble_FPC_Cache
{
    /** @var Memcached */
    protected $_memcache;

    protected function _init()
    {
        if (!extension_loaded('memcached')) {
            throw new Exception('The memcached extension must be loaded');
        }
        $this->_memcache = new Memcached;
        $hosts = explode(',', $this->_options['memcached_host']);
        $ports = explode(',', $this->_options['memcached_port']);
        $servers = array();
        foreach ($hosts as $i => $host) {
            $servers[] = array($host, $ports[$i]);
        }
        $this->_memcache->addServers($servers);
    }

    protected function _zip($data)
    {
        return $data; // zipping is already done transparently
    }

    protected function _unzip($data)
    {
        return $data; // unzipping is already done transparently
    }

    public function load($id)
    {
        $result = $this->_memcache->get($this->_id($id));
        if ($result) {
            $result = $this->_unzip($result);
        }

        return $result;
    }

    public function save($id, $data, $lifetime = null)
    {
        if (null === $lifetime) {
            $lifetime = $this->getLifetime();
        }

        return @$this->_memcache->set($this->_id($id), $this->_zip($data), $lifetime);
    }

    public function delete($id)
    {
        return $this->_memcache->delete($id);
    }

    public function deletePath($path, $prefix = '', $suffix = '')
    {
        $delimiter = '#';
        foreach ($this->_memcache->getAllKeys() as $key) {
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

        return $this;
    }

    public function flush($ns)
    {
        return $this->_memcache->flush();
    }

    public function test()
    {
        $stats = $this->_memcache->getStats();
        $info = array_shift($stats);

        return isset($info['pid']) && $info['pid'] > 0;
    }

    public function close()
    {
        if ($this->_memcache) {
            $this->_memcache->quit();
        }
    }
}