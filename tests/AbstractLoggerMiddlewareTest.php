<?php

declare(strict_types=1);

namespace GuzzleLogMiddleware\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\TransferStats;
use GuzzleLogMiddleware\LogMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\Test\TestLogger;

abstract class AbstractLoggerMiddlewareTest extends TestCase
{
    /**
     * @var MockHandler
     */
    protected $mockHandler;

    /**
     * @var TestLogger
     */
    protected $logger;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var RequestException
     */
    protected $reason;

    /**
     * @var TransferStats
     */
    protected $stats;

    public function setUp()
    {
        parent::setUp();
        $this->mockHandler = new MockHandler();
        $this->logger = new TestLogger();
        $this->request = new Request('get', 'http://www.test.com/');
        $this->response = new Response(404);
        $this->reason = new RequestException('Not Found', $this->request, $this->response);
        $this->stats = new TransferStats($this->request, $this->response, 0.01);
    }

    /**
     * @param int $code
     * @param array $headers
     * @param string $body
     * @param string $version
     * @param string|null $reason
     * @return $this
     */
    protected function appendResponse(
        int $code = 200,
        array $headers = [],
        string $body = '',
        string $version = '1.1',
        string $reason = null
    ) {
        $this->mockHandler->append(new Response($code, $headers, $body, $version, $reason));
        return $this;
    }

    /**
     * @param array $options
     * @return Client
     */
    protected function createClient(array $options = [])
    {
        $stack = HandlerStack::create($this->mockHandler);
        $stack->unshift($this->createMiddleware());
        return new Client(
            array_merge([
                'handler' => $stack,
            ], $options)
        );
    }

    /**
     * Factory method to create the middleware
     *
     * @return LogMiddleware
     */
    abstract protected function createMiddleware(): LogMiddleware;
}
