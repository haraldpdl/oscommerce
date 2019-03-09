<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\PDO\MySQL\Standard;

use osCommerce\OM\Core\OSCOM;

class PDOStatement extends \osCommerce\OM\Core\PDOStatement
{
    protected $pdo;

    protected function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function execute($input_parameters = null): bool
    {
        try {
            $query_action = mb_strtolower(mb_substr($this->queryString, 0, mb_strpos($this->queryString, ' ')));

            $db_table_prefix = OSCOM::getConfig('db_table_prefix');

            if ($query_action == 'delete') {
                $query_data = explode(' ', $this->queryString, 4);
                $query_table = mb_substr($query_data[2], mb_strlen($db_table_prefix));

                if ($this->pdo->hasForeignKey($query_table)) {
                    // check for RESTRICT constraints first
                    foreach ($this->pdo->getForeignKeys($query_table) as $fk) {
                        if ($fk['on_delete'] == 'restrict') {
                            $Qchild = $this->pdo->prepare('select ' . $fk['to_field'] . ' from ' . $query_data[2] . ' ' . $query_data[3]);

                            foreach ($this->binded_params as $key => $value) {
                                $Qchild->bindValue($key, $value['value'], $value['data_type']);
                            }

                            $Qchild->execute();

                            while ($Qchild->fetch()) {
                                $Qcheck = $this->pdo->prepare('select ' . $fk['from_field'] . ' from ' . $db_table_prefix .  $fk['from_table'] . ' where ' . $fk['from_field'] . ' = "' . $Qchild->value($fk['to_field']) . '" limit 1');
                                $Qcheck->execute();

                                if (count($Qcheck->fetchAll()) === 1) {
                                    throw new \PDOException('RESTRICT constraint condition from table ' . $db_table_prefix .  $fk['from_table']);
                                }
                            }
                        }
                    }

                    $this->pdo->beginTransaction();

                    foreach ($this->pdo->getForeignKeys($query_table) as $fk) {
                        $Qparent = $this->pdo->prepare('select * from ' . $query_data[2] . ' ' . $query_data[3]);

                        foreach ($this->binded_params as $key => $value) {
                            $Qparent->bindValue($key, $value['value'], $value['data_type']);
                        }

                        $Qparent->execute();

                        while ($Qparent->fetch()) {
                            if ($fk['on_delete'] == 'cascade') {
                                $Qdel = $this->pdo->prepare('delete from ' . $db_table_prefix . $fk['from_table'] . ' where ' . $fk['from_field'] . ' = :' . $fk['from_field']);
                                $Qdel->bindValue(':' . $fk['from_field'], $Qparent->value($fk['to_field']));
                                $Qdel->execute();
                            } elseif ($fk['on_delete'] == 'set_null') {
                                $Qupdate = $this->pdo->prepare('update ' . $db_table_prefix . $fk['from_table'] . ' set ' . $fk['from_field'] . ' = null where ' . $fk['from_field'] . ' = :' . $fk['from_field']);
                                $Qupdate->bindValue(':' . $fk['from_field'], $Qparent->value($fk['to_field']));
                                $Qupdate->execute();
                            }
                        }
                    }
                }
            } elseif ($query_action == 'update') {
                $query_data = explode(' ', $this->queryString, 3);
                $query_table = mb_substr($query_data[1], mb_strlen($db_table_prefix));

                if ($this->pdo->hasForeignKey($query_table)) {
                    // check for RESTRICT constraints first
                    foreach ($this->pdo->getForeignKeys($query_table) as $fk) {
                        if ($fk['on_update'] == 'restrict') {
                            $Qchild = $this->pdo->prepare('select ' . $fk['to_field'] . ' from ' . $query_data[2] . ' ' . $query_data[3]);

                            foreach ($this->binded_params as $key => $value) {
                                $Qchild->bindValue($key, $value['value'], $value['data_type']);
                            }

                            $Qchild->execute();

                            while ($Qchild->fetch()) {
                                $Qcheck = $this->pdo->prepare('select ' . $fk['from_field'] . ' from ' . $db_table_prefix .  $fk['from_table'] . ' where ' . $fk['from_field'] . ' = "' . $Qchild->value($fk['to_field']) . '" limit 1');
                                $Qcheck->execute();

                                if (count($Qcheck->fetchAll()) === 1) {
                                    throw new \PDOException('RESTRICT constraint condition from table ' . $db_table_prefix .  $fk['from_table']);
                                }
                            }
                        }
                    }

                    $this->pdo->beginTransaction();

                    foreach ($this->pdo->getForeignKeys($query_table) as $fk) {
                        // check to see if foreign key column value is being changed
                        if (mb_strpos(mb_substr($this->queryString, mb_strpos($this->queryString, ' set ')+4, mb_strpos($this->queryString, ' where ') - mb_strpos($this->queryString, ' set ') - 4), ' ' . $fk['to_field'] . ' ') !== false) {
                            $Qparent = $this->pdo->prepare('select * from ' . $query_data[1] . mb_substr($this->queryString, mb_strrpos($this->queryString, ' where ')));

                            foreach ($this->binded_params as $key => $value) {
                                if (preg_match('/:\b' . mb_substr($key, 1) . '\b/', $Qparent->queryString)) {
                                    $Qparent->bindValue($key, $value['value'], $value['data_type']);
                                }
                            }

                            $Qparent->execute();

                            while ($Qparent->fetch()) {
                                if (($fk['on_update'] == 'cascade') || ($fk['on_update'] == 'set_null')) {
                                    $on_update_value = '';

                                    if ($fk['on_update'] == 'cascade') {
                                        $on_update_value = $this->binded_params[':' . $fk['to_field']]['value'];
                                    }

                                    $Qupdate = $this->pdo->prepare('update ' . $db_table_prefix . $fk['from_table'] . ' set ' . $fk['from_field'] . ' = :' . $fk['from_field'] . ' where ' . $fk['from_field'] . ' = :' . $fk['from_field'] . '_orig');

                                    if (empty($on_update_value)) {
                                        $Qupdate->bindNull(':' . $fk['from_field']);
                                    } else {
                                        $Qupdate->bindValue(':' . $fk['from_field'], $on_update_value);
                                    }

                                    $Qupdate->bindValue(':' . $fk['from_field'] . '_orig', $Qparent->value($fk['to_field']));
                                    $Qupdate->execute();
                                }
                            }
                        }
                    }
                }
            }

            $result = parent::execute($input_parameters);

            if ($this->pdo->inTransaction()) {
                $this->pdo->commit();
            }
        } catch (\PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            trigger_error($e->getMessage());

            $result = false;
        }

        return $result;
    }
}
