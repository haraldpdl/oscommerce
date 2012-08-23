<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\PDO;

  class PostgreSQL extends \osCommerce\OM\Core\PDO {
    public function __construct($server, $username, $password, $database, $port, $driver_options) {
      $this->_server = $server;
      $this->_username = (!empty($username) ? $username : null);
      $this->_password = (!empty($password) ? $password : null);
      $this->_database = $database;
      $this->_port = $port;
      $this->_driver_options = $driver_options;

      return $this->connect();
    }

    public function connect() {
      $dsn_array = array();

      if ( empty($this->_database) ) {
        $this->_database = 'postgres';
      }

      $dsn_array[] = 'dbname=' . $this->_database;

      $dsn_array[] = 'host=' . $this->_server;

      if ( !empty($this->_port) ) {
        $dsn_array[] = 'port=' . $this->_port;
      }

      $dsn = 'pgsql:' . implode(';', $dsn_array);

      $this->_connected = true;

      return parent::__construct($dsn, $this->_username, $this->_password, $this->_driver_options);
    }
  }
?>
