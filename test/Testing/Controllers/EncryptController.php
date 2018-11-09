<?php

namespace SwoftTest\Testing\Controllers;

use Swoft\Encrypt\Bean\Annotation\Encrypt;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;

/**
 * Class RateLimiterController
 * @Controller(prefix="/encrypt")
 * @Encrypt()
 */
class EncryptController
{
    /**
     * @RequestMapping()
     * @Encrypt(after=Encrypt::AFTER_ENCRYPT)
     */
    public function encrypt()
    {
        return request()->raw();
    }

    /**
     * @RequestMapping()
     * @Encrypt(before=Encrypt::BEFORE_DECRYPT)
     */
    public function decrypt()
    {
        return request()->getParsedBody();
    }

    /**
     * @RequestMapping()
     * @Encrypt(after=Encrypt::AFTER_SIGN)
     */
    public function sign()
    {
        return request()->post();
    }

    /**
     * @RequestMapping()
     * @Encrypt(before=Encrypt::BEFORE_VERIFY)
     */
    public function verify()
    {
        return request()->post();
    }
}