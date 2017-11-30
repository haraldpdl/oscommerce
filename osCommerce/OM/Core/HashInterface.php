<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

interface HashInterface
{
    public static function get(string $string): string;
    public static function validate(string $plain, string $hashed): bool;
    public static function canValidate(string $hash): bool;
    public static function canUse(): bool;
}
