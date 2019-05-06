<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

use GuzzleHttp\Client as GuzzleClient;

class HttpRequest
{

/**
 * @param array $data url, header, parameters, method, cafile, certificate, format
 */

    public static function getResponse(array $data)
    {
        if (!isset($data['header']) || !is_array($data['header'])) {
            $data['header'] = [];
        }

        if (!isset($data['parameters'])) {
            $data['parameters'] = '';
        }

        if (!isset($data['method'])) {
            $data['method'] = !empty($data['parameters']) ? 'post' : 'get';
        }

        if (!isset($data['cafile'])) {
            $data['cafile'] = OSCOM::BASE_DIRECTORY . 'External/cacert.pem';
        }

        if (isset($data['format']) && !in_array($data['format'], ['json'])) {
            trigger_error('HttpRequest::getResponse(): Unknown "format": ' . $data['format']);

            unset($data['format']);
        }

        $options = [];

        if (!empty($data['header'])) {
            foreach ($data['header'] as $h) {
                [$key, $value] = explode(':', $h, 2);

                $options['headers'][$key] = $value;

                unset($key);
                unset($value);
            }
        }

        if (isset($data['format']) && ($data['format'] === 'json')) {
            $options['json'] = $data['parameters'];
        } else {
            if (($data['method'] === 'post') && !empty($data['parameters'])) {
                if (!is_array($data['parameters'])) {
                    parse_str($data['parameters'], $output);

                    $data['parameters'] = $output;
                }

                $options['form_params'] = $data['parameters'];
            }
        }

        if (isset($data['cafile']) && is_file($data['cafile'])) {
            $options['verify'] = $data['cafile'];
        }

        if (isset($data['certificate']) && is_file($data['certificate'])) {
            $options['cert'] = $data['certificate'];
        }

        $result = false;

        try {
            $client = new GuzzleClient();
            $response = $client->request($data['method'], $data['url'], $options);

            if ($response->getStatusCode() === 200) {
                $result = $response->getBody()->getContents();

                if (isset($data['format']) && ($data['format'] === 'json')) {
                    $result = json_decode($result, true);
                }
            }
        } catch (\Exception $e) {
            trigger_error($e->getMessage());
        }

        return $result;
    }

/**
 * Set the HTTP status code
 *
 * @param int $code The HTTP status code to set
 * @return boolean
 */

    public static function setResponseCode(int $code): bool
    {
        if (headers_sent()) {
            trigger_error('HttpRequest::setResponseCode() - headers already sent, cannot set response code.', E_USER_ERROR);

            return false;
        }

        http_response_code($code);

        return true;
    }
}
