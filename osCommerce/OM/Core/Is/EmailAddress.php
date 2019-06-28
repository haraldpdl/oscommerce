<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Is;

class EmailAddress implements \osCommerce\OM\Core\IsInterface
{
    public static function execute($value, bool $check_dns = false): bool
    {
        if (empty($value) || (strlen($value) > 191)) {
            return false;
        }

        if (filter_var($value, \FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        if ($check_dns === true) {
            $domain = explode('@', $value, 2);

            // international domains (eg, containing german umlauts) are converted to punycode
            if (mb_detect_encoding($domain[1], 'ASCII', true) !== 'ASCII') {
                $domain[1] = idn_to_ascii($domain[1]);
            }

            if ($domain[1] === false) {
                return false;
            }

            if (!checkdnsrr($domain[1], 'MX') && !checkdnsrr($domain[1], 'A')) {
                return false;
            }
        }

        return true;
    }
}
