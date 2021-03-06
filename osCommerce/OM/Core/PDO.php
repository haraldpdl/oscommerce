<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

use osCommerce\OM\Core\{
    HTML,
    OSCOM,
    Registry
};

class PDO
{
    protected $instance;
    protected $server;
    protected $username;
    protected $password;
    protected $database;
    protected $port;
    protected $table_prefix;
    protected $driver;
    protected $driver_options = [];
    protected $driver_parent;
    protected $options = [
        'prefix_tables' => true
    ];

    public static function initialize(string $server = null, string $username = null, string $password = null, string $database = null, int $port = null, string $driver = null, array $driver_options = [], array $options = []): PDO
    {
        if (!isset($server)) {
            $server = OSCOM::getConfig('db_server');
        }

        if (!isset($username) && OSCOM::configExists('db_server_username')) {
            $username = OSCOM::getConfig('db_server_username');
        }

        if (!isset($password) && OSCOM::configExists('db_server_password')) {
            $password = OSCOM::getConfig('db_server_password');
        }

        if (!isset($database) && OSCOM::configExists('db_database')) {
            $database = OSCOM::getConfig('db_database');
        }

        if (!isset($port) && OSCOM::configExists('db_server_port')) {
            $port = (int)OSCOM::getConfig('db_server_port');
        }

        if (!isset($driver) && OSCOM::configExists('db_driver')) {
            $driver = OSCOM::getConfig('db_driver');
        }

        if (!isset($driver_options[\PDO::ATTR_PERSISTENT]) && OSCOM::configExists('db_server_persistent_connections')) {
            if (OSCOM::getConfig('db_server_persistent_connections') === 'true') {
                $driver_options[\PDO::ATTR_PERSISTENT] = true;
            }
        }

        if (!isset($driver_options[\PDO::ATTR_ERRMODE])) {
            $driver_options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
        }

        if (!isset($driver_options[\PDO::ATTR_DEFAULT_FETCH_MODE])) {
            $driver_options[\PDO::ATTR_DEFAULT_FETCH_MODE] = \PDO::FETCH_ASSOC;
        }

        if (!isset($driver_options[\PDO::ATTR_STATEMENT_CLASS])) {
            $driver_options[\PDO::ATTR_STATEMENT_CLASS] = [
                'osCommerce\\OM\\Core\\PDOStatement'
            ];
        }

        $class = 'osCommerce\\OM\\Core\\PDO\\' . $driver;
        $object = new $class($server, $username, $password, $database, $port, $driver_options);

        $object->driver = $driver;
        $object->options = array_merge($object->options, $options);

        return $object;
    }

    public function exec(string $statement)
    {
        if (!isset($this->instance)) {
            $this->connect();
        }

        if ($this->options['prefix_tables'] === true) {
            $statement = $this->autoPrefixTables($statement);
        }

        try {
            $result = $this->instance->exec($statement);
        } catch (\PDOException $e) {
            trigger_error($e->getMessage());

            $result = false;
        }

        return $result;
    }

    public function prepare(string $statement, array $driver_options = [])
    {
        if (!isset($this->instance)) {
            $this->connect();
        }

        if ($this->options['prefix_tables'] === true) {
            $statement = $this->autoPrefixTables($statement);
        }

        try {
            $result = $this->instance->prepare($statement, $driver_options);
            $result->setQueryCall('prepare');
        } catch (\PDOException $e) {
            trigger_error($e->getMessage());

            $result = false;
        }

        return $result;
    }

    public function query(string $statement, ...$params)
    {
        if (!isset($this->instance)) {
            $this->connect();
        }

        if ($this->options['prefix_tables'] === true) {
            $statement = $this->autoPrefixTables($statement);
        }

        try {
            $result = $this->instance->query($statement, ...$params);
            $result->setQueryCall('query');
        } catch (\PDOException $e) {
            trigger_error($e->getMessage());

            $result = false;
        }

        return $result;
    }

    public function get($table, $fields, array $where = null, $order = null, $limit = null, array $options = null)
    {
        if (!is_array($table)) {
            $table = [
                $table
            ];
        }

        if ((!isset($options['prefix_tables']) && ($this->options['prefix_tables'] === true)) || (isset($options['prefix_tables']) && ($options['prefix_tables'] === true))) {
            $new_table = [];

            array_walk($table, function($v, $k) use (&$new_table) { // array_walk cannot alter keys
                if (is_array($v)) {
                    if ((mb_strlen($k) < 7) || (mb_substr($k, 0, 7) != ':table_')) {
                        $k = ':table_' . $k;
                    }

                    if ((mb_strlen($v['rel']) < 7) || (mb_substr($v['rel'], 0, 7) != ':table_')) {
                        $v['rel'] = ':table_' . $v['rel'];
                    }
                } else {
                    if ((mb_strlen($v) < 7) || (mb_substr($v, 0, 7) != ':table_')) {
                        $v = ':table_' . $v;
                    }
                }

                $new_table[$k] = $v;
            });

            $table = $new_table;
        }

        if (!is_array($fields)) {
            $fields = [
                $fields
            ];
        }

        if (isset($order) && !is_array($order)) {
            $order = [
                $order
            ];
        }

        if (isset($limit)) {
            if (is_array($limit) && (count($limit) === 2) && is_numeric($limit[0]) && is_numeric($limit[1])) {
                $limit = implode(', ', $limit);
            } elseif (!is_numeric($limit)) {
                $limit = null;
            }
        }

        $statement = 'select ' . implode(', ', $fields) . ' from ';

        $it_table = new \CachingIterator(new \ArrayIterator($table), \CachingIterator::TOSTRING_USE_CURRENT);

        foreach ($it_table as $tk => $tv) {
            if (is_array($tv)) {
                $statement .= $tk . ' left join ' . $tv['rel'] . ' on (' . $tv['on'] . ')';
            } else {
                $statement .= $tv;
            }

            if ($it_table->hasNext()) {
                $statement .= ', ';
            }
        }

        if (!isset($where) && !isset($options['cache'])) {
            if (isset($order)) {
                $statement .= ' order by ' . implode(', ', $order);
            }

            if (isset($limit)) {
                $statement .= ' limit ' . $limit;
            }

            return $this->query($statement);
        }

        $it_where = [];

        if (isset($where)) {
            $statement .= ' where ';

            $counter = 0;

            $it_where = new \CachingIterator(new \ArrayIterator($where), \CachingIterator::TOSTRING_USE_CURRENT);

            foreach ($it_where as $key => $value) {
                if (is_array($value)) {
                    if (isset($value['val'])) {
                        $statement .= $key . ' ' . (isset($value['op']) ? $value['op'] : '=') . ' ' . ($value['val'] === 'null' ? 'null' : ':cond_' . $counter);
                    }

                    if (isset($value['rel'])) {
                        if (isset($value['val'])) {
                            $statement .= ' and ';
                        }

                        if (is_array($value['rel'])) {
                            $it_rel = new \CachingIterator(new \ArrayIterator($value['rel']), \CachingIterator::TOSTRING_USE_CURRENT);

                            foreach ($it_rel as $rel) {
                                $statement .= $key . ' = ' . $rel;

                                if ($it_rel->hasNext()) {
                                    $statement .= ' and ';
                                }
                            }
                        } else {
                            $statement .= $key . ' ' . (isset($value['op']) ? $value['op'] : '=') . ' ' . $value['rel'];
                        }
                    }
                } else {
                    if ($value === 'null') {
                        $statement .= $key . ' is null';
                    } else {
                        $statement .= $key . ' = :cond_' . $counter;
                    }
                }

                if ($it_where->hasNext()) {
                    $statement .= ' and ';
                }

                $counter++;
            }
        }

        if (isset($order)) {
            $statement .= ' order by ' . implode(', ', $order);
        }

        if (isset($limit)) {
            $statement .= ' limit ' . $limit;
        }

        $Q = $this->prepare($statement);

        if (isset($where)) {
            $counter = 0;

            foreach ($it_where as $value) {
                if (is_array($value)) {
                    if (isset($value['val']) && ($value['val'] !== 'null')) {
                        $Q->bindValue(':cond_' . $counter, $value['val']);
                    }
                } else {
                    if ($value !== 'null') {
                        $Q->bindValue(':cond_' . $counter, $value);
                    }
                }

                $counter++;
            }
        }

        if (isset($options['cache'])) {
            $Q->setCache($options['cache']['key'], $options['cache']['expire'] ?? 0, $options['cache']['store_empty'] ?? false);
        }

        $Q->execute();

        return $Q;
    }

    public function save(string $table, array $data, array $where_condition = null, array $options = null)
    {
        if ((!isset($options['prefix_tables']) && ($this->options['prefix_tables'] === true)) || (isset($options['prefix_tables']) && ($options['prefix_tables'] === true))) {
            if ((mb_strlen($table) < 7) || (mb_substr($table, 0, 7) != ':table_')) {
                $table = ':table_' . $table;
            }
        }

        if (isset($where_condition)) {
            $statement = 'update ' . $table . ' set ';

            foreach ($data as $c => $v) {
                if ($v == 'now()' || $v === 'null') {
                    $statement .= $c . ' = ' . $v . ', ';
                } else {
                    $statement .= $c . ' = :new_' . $c . ', ';
                }
            }

            $statement = mb_substr($statement, 0, -2) . ' where ';

            foreach (array_keys($where_condition) as $c) {
                $statement .= $c . ' = :cond_' . $c . ' and ';
            }

            $statement = mb_substr($statement, 0, -5);

            $Q = $this->prepare($statement);

            foreach ($data as $c => $v) {
                if ($v != 'now()' && $v !== 'null') {
                    $Q->bindValue(':new_' . $c, $v);
                }
            }

            foreach ($where_condition as $c => $v) {
                $Q->bindValue(':cond_' . $c, $v);
            }

            $Q->execute();

            return $Q->rowCount();
        } else {
            $is_prepared = false;

            $statement = 'insert into ' . $table . ' (' . implode(', ', array_keys($data)) . ') values (';

            foreach ($data as $c => $v) {
                if ($v == 'now()' || $v === 'null' || is_null($v)) {
                    $statement .= ($v ?? 'null') . ', ';
                } else {
                    if ($is_prepared === false) {
                        $is_prepared = true;
                    }

                    $statement .= ':' . $c . ', ';
                }
            }

            $statement = mb_substr($statement, 0, -2) . ')';

            if ($is_prepared === true) {
                $Q = $this->prepare($statement);

                foreach ($data as $c => $v) {
                    if ($v != 'now()' && $v !== 'null' && !is_null($v)) {
                        $Q->bindValue(':' . $c, $v);
                    }
                }

                $Q->execute();

                return $Q->rowCount();
            } else {
                return $this->exec($statement);
            }
        }

        return false;
    }

    public function delete(string $table, array $where_condition, array $options = null): int
    {
        if ((!isset($options['prefix_tables']) && ($this->options['prefix_tables'] === true)) || (isset($options['prefix_tables']) && ($options['prefix_tables'] === true))) {
            if ((mb_strlen($table) < 7) || (mb_substr($table, 0, 7) != ':table_')) {
                $table = ':table_' . $table;
            }
        }

        $statement = 'delete from ' . $table . ' where ';

        foreach (array_keys($where_condition) as $c) {
            $statement .= $c . ' = :cond_' . $c . ' and ';
        }

        $statement = mb_substr($statement, 0, -5);

        $Q = $this->prepare($statement);

        foreach ($where_condition as $c => $v) {
            $Q->bindValue(':cond_' . $c, $v);
        }

        $Q->execute();

        return $Q->rowCount();
    }

    public function call(string $procedure, array $data = null, string $container = '_Global')
    {
        if (mb_strpos($procedure, '\\') !== false) {
            $override_ns = explode('\\', $procedure);

            $bt_classes = [
                'osCommerce\\OM\\Core\\' . implode('\\', array_slice($override_ns, 0, -1)) . '\\' . $container
            ];

            $procedure = end($override_ns);
        } else {
            $bt = debug_backtrace(0, 2);

            $bt_classes = [
                $bt[1]['class']
            ];

            $bt_classes = array_merge($bt_classes, class_parents($bt[1]['class']));
        }

        foreach ($bt_classes as $bt_class) {
            $ns_class = explode('\\', $bt_class);

            if (implode('\\', array_slice($ns_class, 0, 2)) == 'osCommerce\\OM') {
                $sql_class = implode('\\', array_slice($ns_class, 0, -1)) . '\\SQL\\' . end($ns_class);

                $driver = $this->getDriver();

                if (!class_exists($sql_class . '\\' . $driver . '\\' . $procedure)) {
                    if ($this->hasDriverParent() && class_exists($sql_class . '\\' . $this->getDriverParent() . '\\' . $procedure)) {
                        $driver = $this->getDriverParent();
                    } else {
                        $driver = null;
                    }
                }

                $callable = [
                    $sql_class . '\\' . (isset($driver) ? $driver . '\\' : '') . $procedure,
                    'execute'
                ];

                if (is_callable($callable)) {
                    return call_user_func($callable, $data);
                }
            }
        }

        trigger_error('OSCOM\\PDO::call(): cannot call: ' . $bt_classes[0] . '::' . $procedure);
    }

    public function getBatchFrom(int $pageset, int $max_results): int
    {
        return max(($pageset * $max_results) - $max_results, 0);
    }

    public function setTablePrefix(string $prefix)
    {
        $this->table_prefix = $prefix;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function getDriverParent(): string
    {
        return $this->driver_parent;
    }

    public function hasDriverParent(): bool
    {
        return isset($this->driver_parent);
    }

    public function importSQL(string $sql_file, string $table_prefix = null): bool
    {
        try {
            if (is_file($sql_file)) {
                $import_queries = file_get_contents($sql_file);

                if ($import_queries === false) {
                    throw new \Exception('OSCOM\PDO::importSQL(): Cannot read SQL import file: ' . $sql_file);
                }
            } else {
                throw new \Exception('OSCOM\PDO::importSQL(): SQL import file does not exist: ' . $sql_file);
            }
        } catch (\Exception $e) {
            trigger_error($e->getMessage());

            return false;
        }

        set_time_limit(0);

        $sql_queries = [];
        $sql_length = mb_strlen($import_queries);
        $pos = mb_strpos($import_queries, ';');

        for ($i = $pos; $i < $sql_length; $i++) {
// remove comments
            if (($import_queries[0] == '#') || (mb_substr($import_queries, 0, 2) == '--')) {
                $import_queries = ltrim(mb_substr($import_queries, mb_strpos($import_queries, "\n")));
                $sql_length = mb_strlen($import_queries);
                $i = mb_strpos($import_queries, ';') - 1;
                continue;
            }

            if ($import_queries[($i+1)] == "\n") {
                $next = '';

                for ($j = ($i + 2); $j < $sql_length; $j++) {
                    if (!empty($import_queries[$j])) {
                        $next = mb_substr($import_queries, $j, 6);

                        if (($next[0] == '#') || (mb_substr($next, 0, 2) == '--')) {
// find out where the break position is so we can remove this line (#comment line)
                            for ($k = $j; $k < $sql_length; $k++) {
                                if ($import_queries[$k] == "\n") {
                                    break;
                                }
                            }

                            $query = mb_substr($import_queries, 0, $i + 1);

                            $import_queries = mb_substr($import_queries, $k);

// join the query before the comment appeared, with the rest of the dump
                            $import_queries = $query . $import_queries;
                            $sql_length = mb_strlen($import_queries);
                            $i = mb_strpos($import_queries, ';') - 1;
                            continue 2;
                        }

                        break;
                    }
                }

                if (empty($next)) { // get the last insert query
                    $next = 'insert';
                }

                if ((mb_strtoupper($next) == 'DROP T') || (mb_strtoupper($next) == 'CREATE') || (mb_strtoupper($next) == 'INSERT') || (mb_strtoupper($next) == 'ALTER ') || (mb_strtoupper($next) == 'SET FO')) {
                    $next = '';

                    $sql_query = mb_substr($import_queries, 0, $i);

                    if (isset($table_prefix)) {
                        if (mb_strtoupper(mb_substr($sql_query, 0, 25)) == 'DROP TABLE IF EXISTS OSC_') {
                            $sql_query = 'DROP TABLE IF EXISTS ' . $table_prefix . mb_substr($sql_query, 25);
                        } elseif (mb_strtoupper(mb_substr($sql_query, 0, 17)) == 'CREATE TABLE OSC_') {
                            $sql_query = 'CREATE TABLE ' . $table_prefix . mb_substr($sql_query, 17);
                        } elseif (mb_strtoupper(mb_substr($sql_query, 0, 16)) == 'INSERT INTO OSC_') {
                            $sql_query = 'INSERT INTO ' . $table_prefix . mb_substr($sql_query, 16);
                        } elseif (mb_strtoupper(mb_substr($sql_query, 0, 12)) == 'CREATE INDEX') {
                            $sql_query = mb_substr($sql_query, 0, mb_stripos($sql_query, ' on osc_')) . ' on ' . $table_prefix . mb_substr($sql_query, mb_stripos($sql_query, ' on osc_') + 8);
                        }
                    }

                    $sql_queries[] = trim($sql_query);

                    $import_queries = ltrim(mb_substr($import_queries, $i + 1));
                    $sql_length = mb_strlen($import_queries);
                    $i = mb_strpos($import_queries, ';') - 1;
                }
            }
        }

        $error = false;

        foreach ($sql_queries as $q) {
            if ($this->exec($q) === false) {
                $error = true;

                break;
            }
        }

        return !$error;
    }

    public static function getBatchTotalPages(string $text, int $pageset_number = 1, int $total): string
    {
        $pageset_number = (is_numeric($pageset_number) ? $pageset_number : 1);

        if ($total < 1) {
            $from = 0;
        } else {
            $from = max(($pageset_number * MAX_DISPLAY_SEARCH_RESULTS) - MAX_DISPLAY_SEARCH_RESULTS, 1);
        }

        $to = min($pageset_number * MAX_DISPLAY_SEARCH_RESULTS, $total);

        return sprintf($text, $from, $to, $total);
    }

    public static function getBatchPageLinks(string $batch_keyword = 'page', int $total, string $parameters = '', bool $with_pull_down_menu = true): string
    {
        $batch_number = (isset($_GET[$batch_keyword]) && is_numeric($_GET[$batch_keyword]) ? $_GET[$batch_keyword] : 1);
        $number_of_pages = ceil($total / MAX_DISPLAY_SEARCH_RESULTS);

        if ($number_of_pages > 1) {
            $string = static::getBatchPreviousPageLink($batch_keyword, $parameters);

            if ($with_pull_down_menu === true) {
                $string .= static::getBatchPagesPullDownMenu($batch_keyword, $total, $parameters);
            }

            $string .= static::getBatchNextPageLink($batch_keyword, $total, $parameters);
        } else {
            $string = sprintf(OSCOM::getDef('result_set_current_page'), 1, 1);
        }

        return $string;
    }

    public static function getBatchPagesPullDownMenu(string $batch_keyword = 'page', int $total, string $parameters = null): string
    {
        $batch_number = (isset($_GET[$batch_keyword]) && is_numeric($_GET[$batch_keyword]) ? $_GET[$batch_keyword] : 1);
        $number_of_pages = ceil($total / MAX_DISPLAY_SEARCH_RESULTS);

        $pages_array = [];

        for ($i = 1; $i <= $number_of_pages; $i++) {
            $pages_array[] = [
                'id' => $i,
                'text' => $i
            ];
        }

        $hidden_parameter = '';

        if (!empty($parameters)) {
            $parameters = explode('&', $parameters);

            foreach ($parameters as $parameter) {
                $keys = explode('=', $parameter, 2);

                if ($keys[0] != $batch_keyword) {
                    $hidden_parameter .= HTML::hiddenField($keys[0], (isset($keys[1]) ? $keys[1] : ''));
                }
            }
        }

        $string = '<form action="' . OSCOM::getLink(null, null) . '" action="get">' . $hidden_parameter .
                  sprintf(OSCOM::getDef('result_set_current_page'), HTML::selectMenu($batch_keyword, $pages_array, $batch_number, 'onchange="this.form.submit();"'), $number_of_pages) .
                  HTML::hiddenSessionIDField() . '</form>';

        return $string;
    }

    public static function getBatchPreviousPageLink(string $batch_keyword = 'page', string $parameters = null): string
    {
        $batch_number = (isset($_GET[$batch_keyword]) && is_numeric($_GET[$batch_keyword]) ? $_GET[$batch_keyword] : 1);

        if (!empty($parameters)) {
            $parameters .= '&';
        }

        $back_string = HTML::icon('nav_back.png', OSCOM::getDef('result_set_previous_page'));
        $back_grey_string = HTML::icon('nav_back_grey.png', OSCOM::getDef('result_set_previous_page'));

        if ($batch_number > 1) {
            $string = HTML::link(OSCOM::getLink(null, null, $parameters . $batch_keyword . '=' . ($batch_number - 1)), $back_string);
        } else {
            $string = $back_grey_string;
        }

        $string .= '&nbsp;';

        return $string;
    }

    public static function getBatchNextPageLink(string $batch_keyword = 'page', int $total, string $parameters = null): string
    {
        $batch_number = (isset($_GET[$batch_keyword]) && is_numeric($_GET[$batch_keyword]) ? $_GET[$batch_keyword] : 1);
        $number_of_pages = ceil($total / MAX_DISPLAY_SEARCH_RESULTS);

        if (!empty($parameters)) {
            $parameters .= '&';
        }

        $forward_string = HTML::icon('nav_forward.png', OSCOM::getDef('result_set_next_page'));
        $forward_grey_string = HTML::icon('nav_forward_grey.png', OSCOM::getDef('result_set_next_page'));

        $string = '&nbsp;';

        if (($batch_number < $number_of_pages) && ($number_of_pages != 1)) {
            $string .= HTML::link(OSCOM::getLink(null, null, $parameters . $batch_keyword . '=' . ($batch_number + 1)), $forward_string);
        } else {
            $string .= $forward_grey_string;
        }

        return $string;
    }

    protected function autoPrefixTables(string $statement): string
    {
        if (!isset($this->table_prefix) && OSCOM::configExists('db_table_prefix')) {
            $this->table_prefix = OSCOM::getConfig('db_table_prefix');
        }

        $prefix = $this->table_prefix ?? '';

        $statement = str_replace(':table_', $prefix, $statement);

        return $statement;
    }

    public function __call(string $name, array $arguments)
    {
        if (isset($this->instance)) {
            $callable = [
                $this->instance,
                $name
            ];

            if (is_callable($callable)) {
                return call_user_func_array($callable, $arguments);
            }
        }

        trigger_error('OSCOM\PDO::__call(): Call to undefined method ' . __CLASS__ . '::' . $name . '()', E_USER_ERROR);

        exit;
    }

    public static function __callStatic(string $name, array $arguments)
    {
        $callable = [
            '\PDO',
            $name
        ];

        if (is_callable($callable)) {
            return forward_static_call_array($callable, $arguments);
        }

        trigger_error('OSCOM\PDO::__callStatic(): Call to undefined method ' . __CLASS__ . '::' . $name . '()', E_USER_ERROR);

        exit;
    }
}
