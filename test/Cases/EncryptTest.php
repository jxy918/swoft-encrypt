<?php
/**
 * Created by PhpStorm.
 * User: zcm
 * Date: 2018/11/9
 * Time: 10:07
 */

namespace SwoftTest\Encrypt;

use Swoft\App;
use Swoft\Encrypt\Handler\EncryptHandler;

/**
 * Class EncryptTest
 * @package SwoftTest\Encrypt
 */
class EncryptTest extends AbstractTestCase
{
    /**
     * @var EncryptHandler
     */
    private $handler;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        $this->handler = App::getBean(EncryptHandler::class);
        parent::__construct($name, $data, $dataName);
    }

    /**
     * @throws \Swoft\Exception\Exception
     */
    public function testEncrypt()
    {
        $res = $this->raw('/encrypt/encrypt', "encrypt");
        $this->assertEquals("encrypt", $this->handler->decrypt($res));
    }

    public function testDecrypt()
    {
        $res = $this->raw('/encrypt/decrypt', $this->handler->encrypt("encrypt"));
        $this->assertEquals("encrypt", $res);
    }

    /**
     * @throws \Swoft\Exception\Exception
     */
    public function testSign()
    {
        $params = [
            "name" => "Tom",
            "age" => 20
        ];
        $res = $this->request("POST", "/encrypt/sign", $params)->getBody()->getContents();
        $res = json_decode($res, true);
        $res = $this->handler->verify(http_build_query($res));
        $this->assertEquals($params, $res);
    }

    public function testVerify()
    {
        $params = [
            "name" => "Tom",
            "age" => 20
        ];
        $res = $this->raw("/encrypt/verify", http_build_query($this->handler->sign($params)));
        $res = json_decode($res, true);
        $this->assertEquals($params, $res);
    }
}