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
    Registry
};

/**
 * Represents a prepared statement and, after the statement is executed, an
 * associated result set.
 */

class PDOStatement extends \PDOStatement
{
    protected $is_error = false;
    protected $binded_params = [];
    protected $cache_key;
    protected $cache_expire;
    protected $cache_data;
    protected $cache_read = false;
    protected $cache_empty = false;
    protected $query_call;
    protected $result;

    public function bindValue($parameter, $value, $data_type = \PDO::PARAM_STR): bool
    {
        $this->binded_params[$parameter] = [
            'value' => $value,
            'data_type' => $data_type
        ];

        try {
            $result = parent::bindValue($parameter, $value, $data_type);
        } catch (\PDOException $e) {
            trigger_error($e->getMessage());

            $result = false;
        }

        return $result;
    }

    public function bindInt(string $parameter, $value): bool
    {
        return $this->bindValue($parameter, $value, \PDO::PARAM_INT);
    }

    public function bindBool(string $parameter, $value): bool
    {
        return $this->bindValue($parameter, $value, \PDO::PARAM_BOOL);
    }

    public function bindNull(string $parameter): bool
    {
        return $this->bindValue($parameter, null, \PDO::PARAM_NULL);
    }

    public function execute($input_parameters = null): bool
    {
        if (isset($this->cache_key)) {
            $OSCOM_Cache = Registry::get('Cache');

            if ($OSCOM_Cache->read($this->cache_key, $this->cache_expire)) {
                $this->cache_data = $OSCOM_Cache->getCache();

                $this->cache_read = true;
            }
        }

        if ($this->cache_read === false) {
            try {
                parent::execute($input_parameters);
            } catch (\PDOException $e) {
                $this->is_error = true;

                trigger_error($e->getMessage());
                trigger_error($this->queryString);
            }
        }

        return ($this->is_error === false);
    }

    public function fetch($fetch_style = \PDO::FETCH_ASSOC, $cursor_orientation = \PDO::FETCH_ORI_NEXT, $cursor_offset = 0)
    {
        if ($this->cache_read === true) {
            if (is_array($this->cache_data)) {
                $this->result = current($this->cache_data);

                if ($this->result !== false) {
                    next($this->cache_data);
                }
            } else {
                $this->result = $this->cache_data;
            }
        } else {
            try {
                $this->result = parent::fetch($fetch_style, $cursor_orientation, $cursor_offset);

                if (isset($this->cache_key)) {
                    if (!isset($this->cache_data)) {
                        $this->cache_data = [];
                    }

                    $this->cache_data[] = $this->result;
                }
            } catch (\PDOException $e) {
                trigger_error($e->getMessage());

                $this->result = false;
            }
        }

        return $this->result;
    }

    public function fetchAll($fetch_style = \PDO::FETCH_ASSOC, $fetch_argument = null, $ctor_args = null)
    {
        if ($this->cache_read === true) {
            $this->result = $this->cache_data;
        } else {
            try {
// PDOStatement::fetchAll() weird signature
                if (isset($fetch_argument) && isset($ctor_args)) {
                    $this->result = parent::fetchAll($fetch_style, $fetch_argument, $ctor_args);
                } elseif (isset($fetch_argument)) {
                    $this->result = parent::fetchAll($fetch_style, $fetch_argument);
                } else {
                    $this->result = parent::fetchAll($fetch_style);
                }

                if (isset($this->cache_key)) {
                    $this->cache_data = $this->result;
                }
            } catch (\PDOException $e) {
                trigger_error($e->getMessage());

                $this->result = false;
            }
        }

        return $this->result;
    }

    public function toArray()
    {
        if (!isset($this->result)) {
            $this->fetch();
        }

        return $this->result;
    }

/**
 * @param string $key The key name for the cache data
 * @param int $expire The amount of minutes the cach data is active for
 * @param bool $cache_empty Save empty cache data (@since v3.0.3)
 * @access public
 */

    public function setCache(string $key, int $expire = 0, bool $cache_empty = false)
    {
        $this->cache_key = basename($key);
        $this->cache_expire = $expire;
        $this->cache_empty = $cache_empty;

        if ($this->query_call != 'prepare') {
            trigger_error('osCommerce\\OM\\Core\\PDOStatement::setCache(): Cannot set cache (\'' . $this->cache_key . '\') on a non-prepare query. Please change the query to a prepare() query.');
        }
    }

    protected function valueMixed(string $column, string $type = 'string')
    {
        if (!isset($this->result)) {
            $this->fetch();
        }

        switch ($type) {
            case 'protected':
                return HTML::outputProtected($this->result[$column]);
                break;

            case 'int':
                return (int)$this->result[$column];
                break;

            case 'decimal':
                return (float)$this->result[$column];
                break;

            case 'string':
            default:
                return $this->result[$column] ?? '';
        }
    }

    public function hasValue(string $column): bool
    {
        if (!isset($this->result)) {
            $this->fetch();
        }

        return !is_null($this->result[$column]) && (mb_strlen($this->result[$column]) > 0);
    }

    public function value(string $column): string
    {
        return $this->valueMixed($column, 'string');
    }

    public function valueProtected(string $column): string
    {
        return $this->valueMixed($column, 'protected');
    }

    public function valueInt(string $column): int
    {
        return $this->valueMixed($column, 'int');
    }

    public function valueDecimal(string $column): float
    {
        return $this->valueMixed($column, 'decimal');
    }

    public function isError(): bool
    {
        return $this->is_error;
    }

/**
 * Return the query string
 */

    public function getQuery(): string
    {
        return $this->queryString;
    }

    public function setQueryCall(string $type)
    {
        $this->query_call = $type;
    }

    public function getQueryCall(): string
    {
        return $this->query_call;
    }

    public function __destruct()
    {
        if (($this->is_error === false) && ($this->cache_read === false) && isset($this->cache_key)) {
            if ($this->cache_empty || !empty($this->cache_data)) {
                Registry::get('Cache')->write($this->cache_data, $this->cache_key);
            }
        }
    }
}
