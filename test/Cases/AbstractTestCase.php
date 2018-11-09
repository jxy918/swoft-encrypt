<?php

namespace SwoftTest\Encrypt;

use PHPUnit\Framework\TestCase;
use Swoft\App;
use Swoft\Helper\ArrayHelper;
use Swoft\Http\Message\Server\Request;
use Swoft\Http\Message\Server\Response;
use Swoft\Http\Message\Stream\SwooleStream;
use Swoft\Testing\SwooleRequest as TestSwooleRequest;
use Swoft\Testing\SwooleResponse as TestSwooleResponse;

/**
 * Class AbstractTestCase
 * @package SwoftTest\RateLimiter
 */
class AbstractTestCase extends TestCase
{
    const ACCEPT_JSON = 'application/json';

    const ACCEPT_RAW = 'text/plain';

    /**
     * Send a mock raw content request
     *
     * @param string $method
     * @param string $uri
     * @param array  $parameters
     * @param array  $headers
     * @param string $rawContent
     * @return string
     */
    public function raw(
        string $uri,
        string $rawContent = ''
    ) {
        return $this->request("POST", $uri, [], self::ACCEPT_RAW, [], $rawContent)->getBody()->getContents();
    }

    /**
     * @param string $uri
     * @return string
     */
    public function get(string $uri): string
    {
        return $this->request("GET", $uri, [], self::ACCEPT_RAW)->getBody()->getContents();
    }

    /**
     * Send a mock request
     *
     * @param string $method
     * @param string $uri
     * @param array  $parameters
     * @param string $accept
     * @param array  $headers
     * @param string $rawContent
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function request(
        string $method,
        string $uri,
        array $parameters = [],
        string $accept = self::ACCEPT_JSON,
        array $headers = [],
        string $rawContent = ''
    ) {
        $method = strtoupper($method);
        $swooleResponse = new TestSwooleResponse();
        $swooleRequest = new TestSwooleRequest();

        $this->buildMockRequest($method, $uri, $parameters, $accept, $swooleRequest, $headers);

        $swooleRequest->setRawContent($rawContent);

        $request = Request::loadFromSwooleRequest($swooleRequest);
        $response = new Response($swooleResponse);

        /** @var \Swoft\Http\Server\ServerDispatcher $dispatcher */
        $dispatcher = App::getBean('serverDispatcher');
        return $dispatcher->dispatch($request, $response);
    }

    /**
     * @param string               $method
     * @param string               $uri
     * @param array                $parameters
     * @param string               $accept
     * @param \Swoole\Http\Request $swooleRequest
     * @param array                $headers
     */
    protected function buildMockRequest(
        string $method,
        string $uri,
        array $parameters,
        string $accept,
        &$swooleRequest,
        array $headers = []
    ) {
        $urlAry = parse_url($uri);
        $urlParams = [];
        if (isset($urlAry['query'])) {
            parse_str($urlAry['query'], $urlParams);
        }
        $defaultHeaders = [
            'host'                      => '127.0.0.1',
            'connection'                => 'keep-alive',
            'cache-control'             => 'max-age=0',
            'user-agent'                => 'PHPUnit',
            'upgrade-insecure-requests' => '1',
            'accept'                    => $accept,
            'dnt'                       => '1',
            'accept-encoding'           => 'gzip, deflate, br',
            'accept-language'           => 'zh-CN,zh;q=0.8,en;q=0.6,it-IT;q=0.4,it;q=0.2',
        ];

        $swooleRequest->fd = 1;
        $swooleRequest->header = ArrayHelper::merge($headers, $defaultHeaders);
        $swooleRequest->server = [
            'request_method'     => $method,
            'request_uri'        => $uri,
            'path_info'          => '/',
            'request_time'       => microtime(),
            'request_time_float' => microtime(true),
            'server_port'        => 80,
            'remote_port'        => 54235,
            'remote_addr'        => '10.0.2.2',
            'master_time'        => microtime(),
            'server_protocol'    => 'HTTP/1.1',
            'server_software'    => 'swoole-http-server',
        ];

        if ($method == 'GET') {
            $swooleRequest->get = $parameters;
        } elseif ($method == 'POST') {
            $swooleRequest->post = $parameters;
        }

        if (! empty($urlParams)) {
            $get = empty($swooleRequest->get) ? [] : $swooleRequest->get;
            $swooleRequest->get = array_merge($urlParams, $get);
        }
    }
}
