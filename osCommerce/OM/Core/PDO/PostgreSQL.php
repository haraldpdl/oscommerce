<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\PDO;

class PostgreSQL extends \osCommerce\OM\Core\PDO
{
    public function __construct(string $server, ?string $username, ?string $password, ?string $database, ?int $port, array $driver_options)
    {
        $this->server = $server;
        $this->username = (!empty($username) ? $username : null);
        $this->password = (!empty($password) ? $password : null);
        $this->database = $database;
        $this->port = $port;
        $this->driver_options = $driver_options;
    }

    public function connect()
    {
        $dsn_array = [];

        if (empty($this->database)) {
            $this->database = 'postgres';
        }

        $dsn_array[] = 'dbname=' . $this->database;

        $dsn_array[] = 'host=' . $this->server;

        if (!empty($this->port)) {
            $dsn_array[] = 'port=' . $this->port;
        }

        $dsn = 'pgsql:' . implode(';', $dsn_array);

        $this->instance = new \PDO($dsn, $this->username, $this->password, $this->driver_options);
    }
}
