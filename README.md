# 如何使用
## 安装组件
>composer require zcmzc/swoft-encrypt
## 配置文件
在/config/properties/app 中添加配置:
```php
    'components' => [
        'custom' => [
            "Swoft\\Encrypt\\"
        ],
    ],
    'encrypt'      => [
        'padding'   => OPENSSL_PKCS1_PADDING,
        'before'    => \Swoft\Encrypt\Bean\Annotation\Encrypt::BEFORE_DECRYPT,
        'after'     => \Swoft\Encrypt\Bean\Annotation\Encrypt::AFTER_ENCRYPT,
        'publicKey' => '@resources/key/rsa_public_key.pem',
        'privateKey'=> '@resources/key/rsa_private_key.pem',
    ]
``` 
## 注解调用
新建控制器`App\Controllers\EncryptController`
```php
<?php

namespace App\Controllers;

use Swoft\Encrypt\Bean\Annotation\Encrypt;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;

/**
 * @Encrypt()
 * @Controller("encrypt")
 */
class EncryptController
{
    /**
     * 无前置操作, 后置加密
     * @RequestMapping()
     * @Encrypt(before="")
     * @return array
     */
    public function encrypt()
    {
        return ['name' => '小红', 'age' => 6666];
    }

    /**
     * 无前置操作, 后置签名
     * @RequestMapping()
     * @Encrypt(before="", after=Encrypt::AFTER_SIGN)
     * @return array
     */
    public function sign()
    {
        return ['name' => '小红', 'age' => 6666];
    }

    /**
     * 前置解密, 无后置操作
     * @RequestMapping()
     * @Encrypt(after="")
     * @return array
     */
    public function decrypt()
    {
        return request()->getParsedBody();
    }

    /**
     * 前置验签, 无后置操作
     * @RequestMapping()
     * @Encrypt(after="", before=Encrypt::BEFORE_VERIFY)
     * @return array
     */
    public function verify()
    {
        return request()->getParsedBody();
    }
}
```
`@Encrypt()`注解里可以设置前置、后置、公钥、私钥
优先级为`方法注解`>`类注解`>`config/app`

前置、后置可设置为空字符串,覆盖低优先级的配置
> 注解调用时,request()方法里是修改后的,方法注入的`Request $request`是未修改的