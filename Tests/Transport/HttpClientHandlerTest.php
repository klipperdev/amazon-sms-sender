<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bridge\SmsSender\Amazon\Tests\Transport;

use Aws\CommandInterface;
use Aws\Exception\AwsException;
use GuzzleHttp\Promise\PromiseInterface;
use Klipper\Bridge\SmsSender\Amazon\Transport\HttpClientHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class HttpClientHandlerTest extends TestCase
{
    /**
     * @var HttpClientInterface|MockObject
     */
    private $client;

    /**
     * @var CommandInterface|MockObject
     */
    private $cmd;

    /**
     * @var MockObject|RequestInterface
     */
    private $request;

    /**
     * @var HttpClientHandler
     */
    private $handler;

    protected function setUp(): void
    {
        $this->client = $this->getMockBuilder(HttpClientInterface::class)->getMock();
        $this->cmd = $this->getMockBuilder(CommandInterface::class)->getMock();
        $this->request = $this->getMockBuilder(RequestInterface::class)->getMock();
        $this->handler = new HttpClientHandler($this->client);
    }

    protected function tearDown(): void
    {
        $this->client = null;
        $this->cmd = null;
        $this->request = null;
        $this->handler = null;
    }

    public function testInvokeWithSuccessResponse(): void
    {
        $uri = $this->getMockBuilder(UriInterface::class)->getMock();
        $uri->expects(static::once())->method('__toString')->willReturn('https://sns.aws.com');

        $reqBodyContent = 'CONTENT';

        $reqBody = $this->getMockBuilder(StreamInterface::class)->getMock();
        $reqBody->expects(static::once())->method('getContents')->willReturn($reqBodyContent);

        $this->request->expects(static::once())->method('getMethod')->willReturn('POST');
        $this->request->expects(static::once())->method('getUri')->willReturn($uri);
        $this->request->expects(static::once())->method('getHeaders')->willReturn([]);
        $this->request->expects(static::once())->method('getBody')->willReturn($reqBody);

        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $response->expects(static::atLeastOnce())->method('getStatusCode')->willReturn(200);
        $response->expects(static::once())->method('getContent')->willReturn(HttpClientContents::getSuccessResponse());
        $response->expects(static::once())->method('getHeaders')->willReturn([
            'header1' => 'string',
            'header2' => ['string 2', 'string 2'],
        ]);

        $this->client->expects(static::once())->method('request')->willReturn($response);

        $res = ($this->handler)($this->cmd, $this->request);
        static::assertInstanceOf(PromiseInterface::class, $res);
    }

    public function testInvokeWithErrorResponse(): void
    {
        $this->expectException(AwsException::class);
        $this->expectExceptionMessage('The request signature we calculated does not match the signature you provided.');

        $uri = $this->getMockBuilder(UriInterface::class)->getMock();
        $uri->expects(static::once())->method('__toString')->willReturn('https://sns.aws.com');

        $reqBodyContent = 'CONTENT';

        $reqBody = $this->getMockBuilder(StreamInterface::class)->getMock();
        $reqBody->expects(static::once())->method('getContents')->willReturn($reqBodyContent);

        $this->request->expects(static::once())->method('getMethod')->willReturn('POST');
        $this->request->expects(static::once())->method('getUri')->willReturn($uri);
        $this->request->expects(static::once())->method('getHeaders')->willReturn([]);
        $this->request->expects(static::once())->method('getBody')->willReturn($reqBody);

        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $response->expects(static::once())->method('getStatusCode')->willReturn(401);
        $response->expects(static::once())->method('getContent')->with(false)
            ->willReturn(HttpClientContents::getErrorResponse())
        ;

        $this->client->expects(static::once())->method('request')->willReturn($response);

        ($this->handler)($this->cmd, $this->request);
    }
}
