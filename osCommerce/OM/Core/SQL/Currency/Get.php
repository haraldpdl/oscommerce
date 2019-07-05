<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\SQL\Currency;

use osCommerce\OM\Core\Registry;

class Get
{
    public static function execute(): array
    {
        $OSCOM_PDO = Registry::get('PDO');

        return $OSCOM_PDO->get('currencies', [
            'currencies_id as id',
            'title',
            'code',
            'symbol_left',
            'symbol_right',
            'decimal_places',
            'value',
            'surcharge'
        ], null, 'title', null, [
            'cache' => [
                'key' => 'currencies'
            ]
        ])->fetchAll();
    }
}
