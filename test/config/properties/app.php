<?php
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://doc.swoft.org
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

return [
    'version' => '1.0',
    'autoInitBean' => true,
    'beanScan' => [
        'Swoft\\Encrypt' => BASE_PATH . '/../src',
        'SwoftTest\\Testing'=> BASE_PATH .'/Testing'
    ],
    'encrypt'      => [
        'publicKey' => '@resources/rsa_public_key.pub',
        'privateKey' => '@resources/rsa_private_key.pem',
        'padding' => OPENSSL_PKCS1_PADDING,
    ],
];
