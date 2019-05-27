<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

abstract class RegistryAbstract
{
    protected $alias;
    protected $value;

    public function getValue()
    {
        return $this->value;
    }

    public function hasAlias(): bool
    {
        return isset($this->alias);
    }

    public function getAlias(): string
    {
        return $this->alias;
    }
}
