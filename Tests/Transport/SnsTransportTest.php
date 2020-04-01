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

use Klipper\Bridge\SmsSender\Amazon\Transport\SnsTransport;
use Klipper\Component\SmsSender\Event\MessageResultEvent;
use Klipper\Component\SmsSender\Exception\TransportResultException;
use Klipper\Component\SmsSender\Mime\Sms;
use Klipper\Component\SmsSender\Transport\Result;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SnsTransportTest extends TestCase
{
    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @var HttpClientInterface|MockObject
     */
    private $client;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var SnsTransport
     */
    private $transport;

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
        $this->client = $this->getMockBuilder(HttpClientInterface::class)->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $this->transport = new SnsTransport(
            'accessKey',
            'secretKey',
            'eu-west-1',
            'SENDER ID',
            'Transactional',
            $this->dispatcher,
            $this->client,
            $this->logger
        );
    }

    protected function tearDown(): void
    {
        $this->dispatcher = null;
        $this->client = null;
        $this->logger = null;
        $this->transport = null;
    }

    public function testGetName(): void
    {
        static::assertEquals('api://accessKey@sns?region=eu-west-1', $this->transport->getName());
    }

    public function testSendWithSuccessMessage(): void
    {
        $message = new Sms();
        $message->to('+2000');

        $clientResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $clientResponse->expects(static::once())->method('getStatusCode')->willReturn(200);
        $clientResponse->expects(static::once())->method('getContent')->with(false)
            ->willReturn(HttpClientContents::getSuccessResponse())
        ;

        $this->client->expects(static::once())
            ->method('request')
            ->willReturn($clientResponse)
        ;

        /** @var null|Result $result */
        $result = null;
        $this->dispatcher->addListener(MessageResultEvent::class, static function (MessageResultEvent $event) use (&$result): void {
            $result = $event->getResult();
        });

        $this->transport->send($message);

        static::assertInstanceOf(Result::class, $result);
        static::assertCount(1, $result->getSuccesses());
        static::assertCount(0, $result->getErrors());
    }

    public function testSendWithErrorMessage(): void
    {
        $this->expectException(TransportResultException::class);
        $this->expectExceptionMessage(str_replace(
            "\n",
            PHP_EOL,
            <<<'EOF'
                Unable to send an SMS for recipients:
                - +2000: The request signature we calculated does not match the signature you provided. (SignatureDoesNotMatch)
                EOF
        ));

        $message = new Sms();
        $message->to('+2000');

        $clientResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $clientResponse->expects(static::atLeastOnce())->method('getStatusCode')->willReturn(403);
        $clientResponse->expects(static::once())->method('getContent')->with(false)
            ->willReturn(HttpClientContents::getErrorResponse())
        ;

        $this->client->expects(static::once())
            ->method('request')
            ->willReturn($clientResponse)
        ;

        /** @var null|Result $result */
        $result = null;
        $this->dispatcher->addListener(MessageResultEvent::class, static function (MessageResultEvent $event) use (&$result): void {
            $result = $event->getResult();
        });

        $this->transport->send($message);
    }

    public function testHasRequiredFrom(): void
    {
        static::assertFalse($this->transport->hasRequiredFrom());
    }
}
