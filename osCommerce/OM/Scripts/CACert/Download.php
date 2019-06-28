<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Scripts\CACert;

use osCommerce\OM\Core\{
    HttpRequest,
    OSCOM,
    RunScript
};

class Download implements \osCommerce\OM\Core\RunScriptInterface
{
    public static function execute()
    {
        $cert_file = OSCOM::BASE_DIRECTORY . 'External/cacert.pem';

        try {
            if (is_file($cert_file)) {
                if (!is_readable($cert_file)) {
                    throw new \Exception('(CACert::Download) Do not have read permissions to CA Certification Bundle: ' . $cert_file);
                } elseif (!is_writable($cert_file)) {
                    throw new \Exception('(CACert::Download) Do not have write permissions to CA Certificate Bundle: ' . $cert_file);
                }
            }

            $pem_sha256_contents = HttpRequest::getResponse([
                'url' => 'https://curl.haxx.se/ca/cacert.pem.sha256'
            ]);

            if (!empty($pem_sha256_contents)) {
                $pem_sha256 = explode(' ', $pem_sha256_contents, 2);

                $cert_file_contents = '';

                if (is_file($cert_file)) {
                    $cert_file_contents = file_get_contents($cert_file);

                    if ($cert_file_contents === false) {
                        throw new \Exception('(CACert::Download) Cannot read contents of CA Certification Bundle: ' . $cert_file);
                    }
                }

                if (!is_file($cert_file) || (hash('sha256', $cert_file_contents) !== $pem_sha256[0])) {
                    $pem_contents = HttpRequest::getResponse([
                        'url' => 'https://curl.haxx.se/ca/cacert.pem'
                    ]);

                    if (!empty($pem_contents)) {
                        if (hash('sha256', $pem_contents) === $pem_sha256[0]) {
                            if (file_put_contents($cert_file, $pem_contents, LOCK_EX) !== false) {
                                throw new \Exception('(CACert::Download) Successfully updated CA Certificate Bundle: ' . $cert_file);
                            } else {
                                throw new \Exception('(CACert::Download) Could not save updated CA Certificate Bundle: ' . $cert_file);
                            }
                        } else {
                            throw new \Exception('(CACert::Download) Downloaded cacert bundle does not match downloaded sha256 checksum');
                        }
                    } else {
                        throw new \Exception('(CACert::Download) Could not download cacert bundle');
                    }
                }
            } else {
                throw new \Exception('(CACert::Download) Could not download cacert bundle sha256 checksum');
            }
        } catch (\Exception $e) {
            RunScript::error($e->getMessage());
        }
    }
}
