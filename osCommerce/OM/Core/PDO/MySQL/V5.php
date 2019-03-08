<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\PDO\MySQL;

class V5 extends \osCommerce\OM\Core\PDO\MySQL\Standard
{
    protected $has_native_fk = true;
    protected $driver_parent = 'MySQL\\Standard';

    public function connect()
    {
// STRICT_ALL_TABLES introduced in MySQL v5.0.2
// Only one init command can be issued (see http://bugs.php.net/bug.php?id=48859)
        $this->driver_options[\PDO::MYSQL_ATTR_INIT_COMMAND] = 'set session sql_mode="STRICT_ALL_TABLES"';

        parent::connect();
    }
}
