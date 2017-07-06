<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FPC_Cache_Redis extends Bubble_FPC_Cache
{
    /** @var Redis */
    protected $_redis;

    protected function _init()
    {
        if (!extension_loaded('redis')) {
            throw new Exception('The redis extension must be loaded');
        }
        $this->_redis = new Redis;
        $host = $this->_options['redis_host'];
        $port = $this->_options['redis_port'];
        $this->_redis->connect($host, $port);
    }

    public function load($id)
    {
        $result = $this->_redis->get($this->_id($id));
        if ($result) {
            $result = $this->_unzip($result);
        }

        return $result;
    }

    public function save($id, $data, $lifetime = null)
    {
        if (null === $lifetime && $this->getLifetime() > 0) {
            $lifetime = $this->getLifetime();
        }

        return @$this->_redis->set($this->_id($id), $this->_zip($data), $lifetime);
    }

    public function delete($id)
    {
        return $this->_redis->delete($id);
    }

    public function deletePath($path, $prefix = '', $suffix = '')
    {
        $pattern = $prefix . FPC_DS . '*' . FPC_DS . $path . '*' . $suffix;
        $keys = $this->_redis->getKeys($pattern);
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return $this;
    }

    public function flush($ns)
    {
        return $this->_redis->flushAll();
    }

    public function test()
    {
        try {
            $this->_redis->ping();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function close()
    {
        if ($this->test()) {
            $this->_redis->close();
        }

        return $this;
    }
}