<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FPC_Cache_Memcache extends Bubble_FPC_Cache
{
    /** @var Memcache */
    protected $_memcache;

    protected function _init()
    {
        if (!extension_loaded('memcache')) {
            throw new Exception('The memcache extension must be loaded');
        }
        $this->_memcache = new Memcache;
        $hosts = explode(',', $this->_options['memcache_host']);
        $ports = explode(',', $this->_options['memcache_port']);
        foreach ($hosts as $i => $host) {
            $this->_memcache->addServer($host, $ports[$i]);
        }
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
        $allSlabs = $this->_memcache->getExtendedStats('slabs');
        foreach ($allSlabs as $slabs) {
            foreach ($slabs as $slabId => $slabMeta) {
                if (!is_int($slabId)) {
                    continue;
                }
                $cache = $this->_memcache->getExtendedStats('cachedump', (int) $slabId, 100000000);
                foreach ($cache as $entries) {
                    if ($entries) {
                        $delimiter = '#';
                        foreach (array_keys($entries) as $key) {
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
                }
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
        $stats = @$this->_memcache->getStats();

        return false !== $stats;
    }

    public function close()
    {
        if ($this->_memcache) {
            $this->_memcache->close();
        }
    }
}