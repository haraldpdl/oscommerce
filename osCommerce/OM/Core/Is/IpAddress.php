<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Is;

class IpAddress implements \osCommerce\OM\Core\IsInterface
{
    public static function execute($value, string $type = 'any'): bool
    {
        if (empty($value)) {
            return false;
        }

        try {
            $options = [];

            if ($type === 'any') {
                $options['flags'] = \FILTER_FLAG_IPV4 | \FILTER_FLAG_IPV6;
            } elseif ($type === 'ipv4') {
                $options['flags'] = \FILTER_FLAG_IPV4;
            } elseif ($type === 'ipv6') {
                $options['flags'] = \FILTER_FLAG_IPV6;
            } else {
                throw new \UnexpectedValueException('Invalid type "' . $type . '". Expecting "any", "ipv4", or "ipv6".');
            }

            return filter_var($value, \FILTER_VALIDATE_IP, $options) !== false;
        } catch (\UnexpectedValueException $e) {
            trigger_error('OSCOM\Is\IpAddress: ' . $e->getMessage());
        }

        return false;
    }
}
