<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FPC_Cache_Filesystem extends Bubble_FPC_Cache
{
    public function load($id)
    {
        $data = false;
        $filepath = FPC_DIR . FPC_DS . $this->_id($id);
        if (file_exists($filepath) && is_file($filepath) && filesize($filepath)) {
            $lifetime = $this->getLifetime();
            if ($lifetime && time() - filemtime($filepath) > $lifetime) {
                // Expired page, it will be generated again
                @unlink($filepath);
            } else {
                $data = $this->_unzip(file_get_contents($filepath));
            }
        }

        return $data;
    }

    public function save($id, $data, $lifetime = null)
    {
        $filepath = FPC_DIR . FPC_DS . $this->_id($id);
        $dir = dirname($filepath);
        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
        }

        return !file_exists($filepath) ? @file_put_contents($filepath, $this->_zip($data)) : false;
    }

    public function delete($id)
    {
        $filepath = FPC_DIR . FPC_DS . $this->_id($id);

        return file_exists($filepath) ? @unlink($filepath) : false;
    }

    public function deletePath($path, $prefix = '', $suffix = '')
    {
        $baseDir = FPC_DIR . FPC_DS . $prefix;
        if (!is_dir($baseDir)) {
            return $this;
        }

        ini_set('max_execution_time', 0);

        $fills = array(
            // [hostname + request hash]
            FPC_DEFAULT_HASH_LENGTH + 1,
            // [hostname + https + request hash]
            FPC_DEFAULT_HASH_LENGTH + 2,
            // [layout hash + hostname + request hash] OR [hostname + request hash + design hash]
            FPC_DEFAULT_HASH_LENGTH * 2 + 1,
            // [layout hash + hostname + https + request hash]
            FPC_DEFAULT_HASH_LENGTH * 2 + 2,
            // [layout hash + hostname + design hash + request hash]
            FPC_DEFAULT_HASH_LENGTH * 3 + 1,
            // [layout hash + hostname + https + design hash + request hash]
            FPC_DEFAULT_HASH_LENGTH * 3 + 2,
        );

        foreach ($fills as $fill) {
            $pattern = $baseDir .FPC_DS. implode(FPC_DS, array_fill(0, $fill, '*')) .FPC_DS. $path . '*' . $suffix;
            foreach (glob($pattern) as $name) {
                @unlink($name);
            }
        }

        return $this;
    }

    public function flush($ns)
    {
        $dir = FPC_DIR;
        if ($ns !== '') {
            $dir .= FPC_DS . $ns;
        }

        return self::rmdirRecursive($dir);
    }

    static function rmdirRecursive($dir, $recursive = true)
    {
        if ($recursive) {
            if (is_dir($dir)) {
                foreach (scandir($dir) as $item) {
                    if (!strcmp($item, '.') || !strcmp($item, '..')) {
                        continue;
                    }
                    self::rmdirRecursive($dir . FPC_DS . $item, $recursive);
                }
                $result = @rmdir($dir);
            } else {
                $result = @unlink($dir);
            }
        } else {
            $result = @rmdir($dir);
        }

        return $result;
    }
}