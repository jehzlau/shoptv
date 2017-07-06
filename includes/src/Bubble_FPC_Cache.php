<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
abstract class Bubble_FPC_Cache
{
    protected $_options;

    protected $_zip = true;

    public function __construct($options = array())
    {
        $this->_options = $options;
        if (isset($options['zip'])) {
            $this->_zip = (bool) $options['zip'];
        }
        $this->_init();
    }

    public function __destruct()
    {
        $this->close();
    }

    static public function factory(array $options = array())
    {
        $default = new Bubble_FPC_Cache_Filesystem($options);
        try {
            $type = isset($options['backend']) ? $options['backend'] : 'filesystem';
            switch ($type) {
                case 'memcached':
                    $cache = new Bubble_FPC_Cache_Memcached($options);
                    break;
                case 'memcache':
                    $cache = new Bubble_FPC_Cache_Memcache($options);
                    break;
                case 'mysql':
                    if (!defined('FPC_DB_CONFIG_FILE')) {
                        throw new Exception('FPC_DB_CONFIG_FILE must be defined');
                    }
                    if (!defined('FPC_DB_TABLE_NAME')) {
                        throw new Exception('FPC_DB_TABLE_NAME must be defined');
                    }
                    $xml = @simplexml_load_file(FPC_DB_CONFIG_FILE);
                    if ($xml) {
                        $config = $xml->global->resources->default_setup->connection;
                        $options['mysql_host']  = (string) $config->host;
                        $options['mysql_user']  = (string) $config->username;
                        $options['mysql_pass']  = (string) $config->password;
                        $options['mysql_db']    = (string) $config->dbname;
                        $options['mysql_table'] = FPC_DB_TABLE_NAME;
                        $options['mysql_init']  = (string) $config->initStatements;
                        $prefix = $xml->global->resources->db->table_prefix;
                        if (!empty($prefix)) {
                            $options['mysql_table_prefix'] = (string) $prefix;
                        }
                    }
                    $cache = new Bubble_FPC_Cache_Mysql($options);
                    break;
                case 'redis':
                    $cache = new Bubble_FPC_Cache_Redis($options);
                    break;
                case 'apc':
                    $cache = new Bubble_FPC_Cache_Apc($options);
                    break;
                default:
                    $cache = $default;
            }
        } catch (Exception $e) {
            $cache = $default;
        }

        return $cache->test() ? $cache : $default;
    }

    abstract public function save($id, $data, $lifetime = null);

    abstract public function load($id);

    abstract public function delete($id);

    abstract public function deletePath($path, $prefix = '', $suffix = '');

    abstract public function flush($ns);

    public function close()
    {
        return $this;
    }

    protected function _init()
    {
        return $this;
    }

    protected function _id($id)
    {
        return $id;
    }

    protected function _zip($data)
    {
        if ($this->_zip && function_exists('gzcompress')) {
            return @gzcompress($data);
        }

        return $data;
    }

    protected function _unzip($data)
    {
        if ($this->_zip && function_exists('gzuncompress')) {
            return @gzuncompress($data);
        }

        return $data;
    }

    public function test()
    {
        return true;
    }

    public function getLifetime()
    {
        return isset($this->_options['lifetime']) ? $this->_options['lifetime'] : false;
    }
}