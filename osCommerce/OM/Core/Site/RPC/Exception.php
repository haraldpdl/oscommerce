<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\RPC;

class Exception extends \Exception
{
    public function __construct(int $code)
    {
        return parent::__construct(null, $code);
    }
}
