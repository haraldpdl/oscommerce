<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\PDO\MySQL;

use osCommerce\OM\Core\OSCOM;

class Standard extends \osCommerce\OM\Core\PDO
{
    protected $has_native_fk = false;
    protected $fkeys = [];

    public function __construct(string $server, ?string $username, ?string $password, ?string $database, ?int $port, array $driver_options)
    {
        $this->server = $server;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->port = $port;
        $this->driver_options = $driver_options;

// Override ATTR_STATEMENT_CLASS to automatically handle foreign key constraints
        if ($this->has_native_fk === false) {
            $this->driver_options[\PDO::ATTR_STATEMENT_CLASS] = [
                'osCommerce\\OM\\Core\\PDO\\MySQL\\Standard\\PDOStatement',
                [
                    $this
                ]
            ];
        }
    }

    public function connect()
    {
        $dsn_array = [];

        if (!empty($this->database)) {
            $dsn_array[] = 'dbname=' . $this->database;
        }

        if ((strpos($this->server, '/') !== false) || (strpos($this->server, '\\') !== false)) {
            $dsn_array[] = 'unix_socket=' . $this->server;
        } else {
            $dsn_array[] = 'host=' . $this->server;

            if (!empty($this->port)) {
                $dsn_array[] = 'port=' . $this->port;
            }
        }

        $dsn_array[] = 'charset=utf8';

        $dsn = 'mysql:' . implode(';', $dsn_array);

        $this->instance = new \PDO($dsn, $this->username, $this->password, $this->driver_options);

        if ((OSCOM::getSite() != 'Setup') && $this->has_native_fk === false) {
            $this->setupForeignKeys();
        }
    }

    public function getForeignKeys(string $table = null): array
    {
        if (isset($table)) {
            return $this->fkeys[$table];
        }

        return $this->fkeys;
    }

    public function setupForeignKeys()
    {
        $Qfk = $this->query('select * from :table_fk_relationships');
        $Qfk->setCache('fk_relationships');
        $Qfk->execute();

        while ($Qfk->fetch()) {
            $this->fkeys[$Qfk->value('to_table')][] = [
                'from_table' => $Qfk->value('from_table'),
                'from_field' => $Qfk->value('from_field'),
                'to_field' => $Qfk->value('to_field'),
                'on_update' => $Qfk->value('on_update'),
                'on_delete' => $Qfk->value('on_delete')
            ];
        }
    }

    public function hasForeignKey(string $table): bool
    {
        return isset($this->fkeys[$table]);
    }
}
