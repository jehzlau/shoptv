<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FPC_Cache_Mysql extends Bubble_FPC_Cache
{
    /**
     * @var PDO
     */
    protected $_pdo;

    /**
     * @var string
     */
    protected $_tableName = '';

    /**
     * @var bool
     */
    protected $_connected = false;

    protected function _init()
    {
        if (!extension_loaded('pdo_mysql')) {
            throw new Exception('The pdo_mysql extension must be loaded');
        }
        if (!defined('FPC_DB_CONFIG_FILE')) {
            throw new Exception('FPC_DB_CONFIG_FILE must be defined');
        }
        try {
            $dsn = sprintf('mysql:host=%s;dbname=%s', $this->_options['mysql_host'], $this->_options['mysql_db']);
            $this->_pdo = new PDO($dsn, $this->_options['mysql_user'], $this->_options['mysql_pass']);
            if (isset($this->_options['mysql_init']) && !empty($this->_options['mysql_init'])) {
                $this->_pdo->query($this->_options['mysql_init']);
            }
            $prefix = '';
            if (isset($this->_options['mysql_prefix']) && !empty($this->_options['mysql_prefix'])) {
                $prefix = $this->_options['mysql_prefix'];
            }
            $this->_tableName = $prefix . $this->_options['mysql_table'];
            $this->_connected = true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function load($id)
    {
        $sth = $this->_pdo->prepare("SELECT * FROM `{$this->_tableName}` WHERE `cache_id` = :id LIMIT 1");
        $sth->execute(array(':id' => $this->_id($id)));
        $row = $sth->fetch(PDO::FETCH_ASSOC);
        if (!empty($row) && $row['lifetime'] > 0 && time() > $row['lifetime']) {
            // Expired data
            $this->delete($id);
            $row = false;
        }

        return $row ? $this->_unzip($row['data']) : false;
    }

    public function save($id, $data, $lifetime = null)
    {
        if (null === $lifetime) {
            $lifetime = $this->getLifetime();
        }
        $sth = $this->_pdo->prepare(
            "INSERT INTO `{$this->_tableName}` (`cache_id`, `data`, `lifetime`) VALUES (:id, :data, :lifetime)
            ON DUPLICATE KEY UPDATE `data` = :data, `lifetime` = :lifetime"
        );
        $result = $sth->execute(array(
            ':id'       => $this->_id($id),
            ':data'     => $this->_zip($data),
            ':lifetime' => $lifetime ? time() + $lifetime : null,
        ));

        return $result;
    }

    public function delete($id)
    {
        $sth = $this->_pdo->prepare("DELETE FROM `{$this->_tableName}` WHERE `cache_id` = :id");

        return $sth->execute(array(':id' => $this->_id($id)));
    }

    public function deletePath($path, $prefix = '', $suffix = '')
    {
        $pattern = $prefix . FPC_DS . '%' . FPC_DS . $path . '%' . $suffix;
        $sth = $this->_pdo->prepare("DELETE FROM `{$this->_tableName}` WHERE `cache_id` LIKE :pattern");

        return $sth->execute(array(':pattern' => $pattern));
    }

    public function flush($ns)
    {
        $sth = $this->_pdo->prepare("TRUNCATE TABLE `{$this->_tableName}`");

        return $sth->execute();
    }

    public function test()
    {
        return $this->_connected;
    }
}