<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Scripts\Database;

use osCommerce\OM\Core\{
    OSCOM,
    PDO,
    RunScript
};

class Utf8mb4 implements \osCommerce\OM\Core\RunScriptInterface
{
    protected static $opts = [];

    public static function execute()
    {
        static::$opts['server'] = RunScript::getOpt('server') ?? OSCOM::getConfig('db_server');
        static::$opts['database'] = RunScript::getOpt('database') ?? OSCOM::getConfig('db_database');
        static::$opts['prefix'] = RunScript::getOpt('prefix') !== null ? RunScript::getOpt('prefix') : OSCOM::configExists('db_table_prefix') ? OSCOM::getConfig('db_table_prefix') : '';

        $username = RunScript::getOpt('username');
        $password = RunScript::getOpt('password');

        if ((PHP_SAPI === 'cli') && !is_null($password) && empty($password)) {
            echo 'Database Password: ';

            $password = trim(fgets(STDIN));
        }

        static::$opts['username'] = !empty($username) ? $username : OSCOM::getConfig('db_server_username');
        static::$opts['password'] = !empty($password) ? $password : OSCOM::getConfig('db_server_password');

        static::$opts['perform-queries'] = strtolower(RunScript::getOpt('perform-queries')) === 'true' ? true : false;

        $show_collations = RunScript::getOpt('show-collations') !== null ? true : false;
        $show_current = RunScript::getOpt('show-current') !== null ? true : false;
        $check_index_size = RunScript::getOpt('check-index-size') !== null ? true : false;
        $update_indexes = RunScript::getOpt('update-indexes') !== null ? true : false;
        $update_database = RunScript::getOpt('update-database') !== null ? true : false;

        if ($show_collations === true) {
            static::showCollations();
        } elseif ($show_current === true) {
            static::showCurrent();
        } elseif ($check_index_size === true) {
            static::checkIndexSize();
        } elseif ($update_indexes === true) {
            static::updateIndexes();
        } elseif ($update_database === true) {
            static::$opts['collation'] = RunScript::getOpt('collation') ?? 'utf8mb4_unicode_ci';

            static::updateDatabase();
        } else {
            static::help();
        }
    }

    protected static function showCollations()
    {
        $PDO = PDO::initialize(static::$opts['server'], static::$opts['username'], static::$opts['password'], static::$opts['database']);

        echo 'Supported Collations:' . RunScript::$linebreak . RunScript::$linebreak;

        $Qcollations = $PDO->query('show collation where Charset = "utf8mb4"');

        while ($Qcollations->fetch()) {
            echo $Qcollations->value('Collation') . RunScript::$linebreak;
        }
    }

    protected static function showCurrent()
    {
        $PDO = PDO::initialize(static::$opts['server'], static::$opts['username'], static::$opts['password'], static::$opts['database']);

        $Qdb = $PDO->prepare('select DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME from information_schema.SCHEMATA where SCHEMA_NAME = :schema_name');
        $Qdb->bindValue(':schema_name', static::$opts['database']);
        $Qdb->execute();

        echo 'Database: ' . static::$opts['database'] . ' CHARSET=' . $Qdb->value('DEFAULT_CHARACTER_SET_NAME') . ' COLLATE=' . $Qdb->value('DEFAULT_COLLATION_NAME') . RunScript::$linebreak;

        $Qtables = $PDO->prepare('select T.TABLE_NAME, T.ENGINE, T.ROW_FORMAT, T.TABLE_COLLATION, CCSA.CHARACTER_SET_NAME from information_schema.TABLES T, information_schema.COLLATION_CHARACTER_SET_APPLICABILITY CCSA where T.TABLE_SCHEMA = :schema and T.TABLE_COLLATION = CCSA.COLLATION_NAME and T.TABLE_NAME like :name order by T.TABLE_NAME');
        $Qtables->bindValue(':schema', static::$opts['database']);
        $Qtables->bindValue(':name', static::$opts['prefix'] . '%');
        $Qtables->execute();

        while ($Qtables->fetch()) {
            echo RunScript::$linebreak . 'Table: ' . $Qtables->value('TABLE_NAME') . ' ENGINE=' . $Qtables->value('ENGINE');

/*
            echo ' ROW_FORMAT=' . $Qtables->value('ROW_FORMAT');
*/

            echo ' CHARSET=' . $Qtables->value('CHARACTER_SET_NAME') . ' COLLATE=' . $Qtables->value('TABLE_COLLATION') . RunScript::$linebreak;

            $Qcol = $PDO->prepare('select COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, CHARACTER_SET_NAME, COLLATION_NAME from information_schema.COLUMNS where TABLE_SCHEMA = :schema and TABLE_NAME = :name');
            $Qcol->bindValue(':schema', static::$opts['database']);
            $Qcol->bindValue(':name', $Qtables->value('TABLE_NAME'));
            $Qcol->execute();

            while ($Qcol->fetch()) {
                if (in_array($Qcol->value('DATA_TYPE'), ['tinytext', 'mediumtext', 'text', 'longtext', 'varchar', 'char'])) {
                    echo '       - ' . $Qcol->value('COLUMN_NAME') . ' ' . $Qcol->value('DATA_TYPE');

                    if (in_array($Qcol->value('DATA_TYPE'), ['varchar', 'char'])) {
                        echo '(' . $Qcol->value('CHARACTER_MAXIMUM_LENGTH') . ')';
                    }

                    echo ' CHARSET=' . $Qcol->value('CHARACTER_SET_NAME') . ' COLLATE=' . $Qcol->value('COLLATION_NAME') . RunScript::$linebreak;
                }
            }
        }
    }

    protected static function checkIndexSize()
    {
        $PDO = PDO::initialize(static::$opts['server'], static::$opts['username'], static::$opts['password'], static::$opts['database']);

        $Qdb = $PDO->prepare('select DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME from information_schema.SCHEMATA where SCHEMA_NAME = :schema_name');
        $Qdb->bindValue(':schema_name', static::$opts['database']);
        $Qdb->execute();

        echo 'Database: ' . static::$opts['database'] . ' CHARSET=' . $Qdb->value('DEFAULT_CHARACTER_SET_NAME') . ' COLLATE=' . $Qdb->value('DEFAULT_COLLATION_NAME') . RunScript::$linebreak;

        $Qindexes = $PDO->prepare('select S.TABLE_NAME, S.INDEX_NAME, S.COLUMN_NAME, C.CHARACTER_MAXIMUM_LENGTH, C.DATA_TYPE from information_schema.STATISTICS S, information_schema.COLUMNS C where S.TABLE_SCHEMA = :schema and S.TABLE_SCHEMA = C.TABLE_SCHEMA and S.TABLE_NAME = C.TABLE_NAME and S.COLUMN_NAME = C.COLUMN_NAME and C.DATA_TYPE in ("varchar", "char") and C.CHARACTER_MAXIMUM_LENGTH > 191');
        $Qindexes->bindValue(':schema', static::$opts['database']);
        $Qindexes->execute();

        while ($Qindexes->fetch()) {
            echo RunScript::$linebreak .
                 'Table: ' . $Qindexes->value('TABLE_NAME') . RunScript::$linebreak .
                 'Index: ' . $Qindexes->value('INDEX_NAME') . RunScript::$linebreak .
                 'Column: ' . $Qindexes->value('COLUMN_NAME') . ' ' . $Qindexes->value('DATA_TYPE') . '(' . $Qindexes->value('CHARACTER_MAXIMUM_LENGTH') . ')' . RunScript::$linebreak;

            $Qmax = $PDO->query('select max(length(' . $Qindexes->value('COLUMN_NAME') . ')) as maxlength from ' . $Qindexes->value('TABLE_NAME'));

            echo 'Current maximum value length: ' . $Qmax->value('maxlength') . RunScript::$linebreak;
        }
    }

    protected static function updateIndexes()
    {
        $PDO = PDO::initialize(static::$opts['server'], static::$opts['username'], static::$opts['password'], static::$opts['database']);

        $Qindexes = $PDO->prepare('select s.TABLE_NAME, s.COLUMN_NAME, c.DATA_TYPE, c.COLLATION_NAME, c.IS_NULLABLE, c.COLUMN_DEFAULT from information_schema.STATISTICS s, information_schema.COLUMNS c where s.TABLE_SCHEMA = :schema and s.TABLE_SCHEMA = c.TABLE_SCHEMA and s.TABLE_NAME = c.TABLE_NAME and s.COLUMN_NAME = c.COLUMN_NAME and c.DATA_TYPE in ("varchar", "char") and c.CHARACTER_MAXIMUM_LENGTH > 191');
        $Qindexes->bindValue(':schema', static::$opts['database']);
        $Qindexes->execute();

        while ($Qindexes->fetch()) {
            $binary = (substr($Qindexes->value('COLLATION_NAME'), -4) == '_bin') ? 'binary' : '';
            $notnull = ($Qindexes->value('IS_NULLABLE') === 'NO') ? 'NOT NULL' : '';
            $default = !empty($Qindexes->value('COLUMN_DEFAULT')) ? 'DEFAULT "' . $Qindexes->value('COLUMN_DEFAULT') . '"' : '';

            $q = 'ALTER TABLE ' . $Qindexes->value('TABLE_NAME') . ' MODIFY COLUMN ' . $Qindexes->value('COLUMN_NAME') . ' ' . $Qindexes->value('DATA_TYPE') . '(191)' . (!empty($binary) ? ' ' . $binary : '') . (!empty($notnull) ? ' ' . $notnull : '') . (!empty($default) ? ' ' . $default : '') . ';';

            if (static::$opts['perform-queries'] === true) {
                $PDO->exec($q);
            } else {
                echo $q . RunScript::$linebreak;
            }
        }
    }

    protected static function updateDatabase()
    {
        $PDO = PDO::initialize(static::$opts['server'], static::$opts['username'], static::$opts['password'], static::$opts['database']);

        $collations = [];

        $Qcollations = $PDO->query('show collation where Charset = "utf8mb4"');

        while ($Qcollations->fetch()) {
            $collations[] = $Qcollations->value('Collation');
        }

        if (!in_array(static::$opts['collation'], $collations)) {
            trigger_error('Collation not available: ' . static::$opts['collation']);
            exit;
        }

        $actions = ['REPAIR', 'OPTIMIZE'];

        $Qdb = $PDO->prepare('select DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME from information_schema.SCHEMATA where SCHEMA_NAME = :schema_name');
        $Qdb->bindValue(':schema_name', static::$opts['database']);
        $Qdb->execute();

        if (($Qdb->value('DEFAULT_CHARACTER_SET_NAME') !== 'utf8mb4') || ($Qdb->value('DEFAULT_COLLATION_NAME') !== static::$opts['collation'])) {
            $q = 'ALTER DATABASE ' . static::$opts['database'] . ' CHARACTER SET = utf8mb4 COLLATE = ' . static::$opts['collation'] . ';';

            if (static::$opts['perform-queries'] === true) {
                $PDO->exec($q);
            } else {
                echo $q . RunScript::$linebreak;
            }
        }

        $Qtables = $PDO->prepare('show table status where Name like :name');
        $Qtables->bindValue(':name', static::$opts['prefix'] . '%');
        $Qtables->execute();

        while ($Qtables->fetch()) {
            $modified = false;

            if ($Qtables->value('Engine') != 'InnoDB') {
                $modified = true;

                $q = 'ALTER TABLE ' . $Qtables->value('Name') . ' ENGINE=InnoDB;';

                if (static::$opts['perform-queries'] === true) {
                    $PDO->exec($q);
                } else {
                    echo $q . RunScript::$linebreak;
                }
            }

/*
            if ($Qtables->value('Row_format') != 'Dynamic') {
                $modified = true;

                $q = 'ALTER TABLE ' . $Qtables->value('Name') . ' ROW_FORMAT=DYNAMIC;';

                if (static::$opts['perform-queries'] === true) {
                    $PDO->exec($q);
                } else {
                    echo $q . RunScript::$linebreak;
                }
            }
*/

            if ($Qtables->value('Collation') != static::$opts['collation']) {
                $modified = true;

                $q = 'ALTER TABLE ' . $Qtables->value('Name') . ' CONVERT TO CHARACTER SET utf8mb4 COLLATE ' . static::$opts['collation'] . ';';

                if (static::$opts['perform-queries'] === true) {
                    $PDO->exec($q);
                } else {
                    echo $q . RunScript::$linebreak;
                }
            }

            $Qcol = $PDO->query('show full columns from ' . $Qtables->value('Name') . ' where Collation is not null');

            while ($Qcol->fetch()) {
                if (in_array($Qcol->value('Collation'), [static::$opts['collation'], 'utf8mb4_bin'])) {
                    continue;
                }

                if (in_array($Qcol->value('Type'), ['tinytext', 'mediumtext', 'text', 'longtext']) || (strpos($Qcol->value('Type'), 'varchar') === 0) || (strpos($Qcol->value('Type'), 'char') === 0)) {
                    $modified = true;

                    $binary = (substr($Qcol->value('Collation'), -4) == '_bin') ? 'binary' : '';
                    $notnull = ($Qcol->value('Null') === 'NO') ? 'NOT NULL' : 'NULL';
                    $default = !empty($Qcol->value('Default')) ? 'DEFAULT "' . $Qcol->value('Default') . '"' : '';

                    $q = 'ALTER TABLE ' . $Qtables->value('Name') . ' MODIFY COLUMN ' . $Qcol->value('Field') . ' ' . $Qcol->value('Type') . (!empty($binary) ? ' ' . $binary : '') . ' CHARACTER SET utf8mb4 COLLATE ' . static::$opts['collation'] . ' ' . $notnull . (!empty($default) ? ' ' . $default : '') . ';';

                    if (static::$opts['perform-queries'] === true) {
                        $PDO->exec($q);
                    } else {
                        echo $q . RunScript::$linebreak;
                    }
                }
            }

            if ($modified === true) {
                foreach ($actions as $action) {
                    $q = $action . ' TABLE ' . $Qtables->value('Name') . ';';

                    if (static::$opts['perform-queries'] === true) {
                        $PDO->exec($q);
                    } else {
                        echo $q . RunScript::$linebreak;
                    }
                }
            }
        }
    }

    protected static function help()
    {
        echo 'Database\\Utf8mb4' . RunScript::$linebreak . RunScript::$linebreak;

        echo 'Parameters:' . RunScript::$linebreak . RunScript::$linebreak;

        echo '--show-collations' . "\t\t" . 'Show a list of available utf8mb4 collations on the database server.' . RunScript::$linebreak;
        echo '--show-current' . "\t\t\t" . 'Show the current table text field collations.' . RunScript::$linebreak;
        echo '--check-index-size' . "\t\t" . 'Show which table indexes are too long and need to be shortened.' . RunScript::$linebreak;
        echo '--update-indexes' . "\t\t" . 'Show queries to be performed to update long table indexes.' . RunScript::$linebreak;
        echo '--update-database' . "\t\t" . 'Show queries to be performed to update the database.' . RunScript::$linebreak;

        echo RunScript::$linebreak;

        echo 'Optional Parameters with --update-database:' . RunScript::$linebreak . RunScript::$linebreak;

        echo '--collation=utf8mb4_unicode_ci' . "\t" . 'The utf8mb4 collation to use.' . RunScript::$linebreak;

        echo RunScript::$linebreak;

        echo 'Optional Parameters:' . RunScript::$linebreak . RunScript::$linebreak;

        echo '--server=SERVER' . "\t\t\t" . 'The server address of the database server.' . RunScript::$linebreak;
        echo '--database=DATABASE' . "\t\t" . 'The name of the database.' . RunScript::$linebreak;
        echo '--username=USERNAME' . "\t\t" . 'The username to connect to the database server with.' . RunScript::$linebreak;
        echo '--password[=PASSWORD]' . "\t\t" . 'Prompt to enter the password, or use the supplied password, to use with the username.' . RunScript::$linebreak;
        echo '--prefix=PREFIX' . "\t\t\t" . 'Only access the database tables with the matching prefix.' . RunScript::$linebreak;
        echo '--perform-queries=FALSE' . "\t\t" . 'When true, perform queries live on the database, or when false, by default, only' . RunScript::$linebreak . "\t\t\t\t" . 'display the queries that are to be performed.' . RunScript::$linebreak;
    }
}
