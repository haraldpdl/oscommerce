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
        $pem_sha256_contents = HttpRequest::getResponse([
            'url' => 'https://curl.haxx.se/ca/cacert.pem.sha256'
        ]);

        if (!empty($pem_sha256_contents)) {
            $pem_sha256 = explode(' ', $pem_sha256_contents, 2);

            if (!is_file(OSCOM::BASE_DIRECTORY . 'External/cacert.pem') || (hash('sha256', file_get_contents(OSCOM::BASE_DIRECTORY . 'External/cacert.pem')) !== $pem_sha256[0])) {
                $pem_contents = HttpRequest::getResponse([
                    'url' => 'https://curl.haxx.se/ca/cacert.pem'
                ]);

                if (!empty($pem_contents)) {
                    if (hash('sha256', $pem_contents) === $pem_sha256[0]) {
                        if (file_put_contents(OSCOM::BASE_DIRECTORY . 'External/cacert.pem', $pem_contents, LOCK_EX) !== false) {
                            RunScript::error('(CACert::Download) Successfully updated CA Certificate Bundle: ' . OSCOM::BASE_DIRECTORY . 'External/cacert.pem');
                        } else {
                            RunScript::error('(CACert::Download) Could not save updated CA Certificate Bundle: ' . OSCOM::BASE_DIRECTORY . 'External/cacert.pem');
                        }
                    } else {
                        RunScript::error('(CACert::Download) Downloaded cacert bundle does not match downloaded sha256 checksum');
                    }
                } else {
                    RunScript::error('(CACert::Download) Could not download cacert bundle');
                }
            }
        } else {
            RunScript::error('(CACert::Download) Could not download cacert bundle sha256 checksum');
        }
    }
}
