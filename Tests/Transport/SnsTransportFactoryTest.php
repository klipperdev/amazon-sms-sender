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
use Klipper\Bridge\SmsSender\Amazon\Transport\SnsTransportFactory;
use Klipper\Component\SmsSender\Tests\TransportFactoryTestCase;
use Klipper\Component\SmsSender\Transport\Dsn;
use Klipper\Component\SmsSender\Transport\TransportFactoryInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SnsTransportFactoryTest extends TransportFactoryTestCase
{
    public function getFactory(): TransportFactoryInterface
    {
        return new SnsTransportFactory($this->getDispatcher(), $this->getClient(), $this->getLogger());
    }

    public function supportsProvider(): iterable
    {
        yield [
            new Dsn('api', 'sns'),
            true,
        ];

        yield [
            new Dsn('http', 'sns'),
            true,
        ];

        yield [
            new Dsn('api', 'example.com'),
            false,
        ];
    }

    public function createProvider(): iterable
    {
        $client = $this->getClient();
        $dispatcher = $this->getDispatcher();
        $logger = $this->getLogger();

        yield [
            new Dsn('api', 'sns', self::USER, self::PASSWORD),
            new SnsTransport(self::USER, self::PASSWORD, null, null, null, $dispatcher, $client, $logger),
        ];
    }

    public function unsupportedSchemeProvider(): iterable
    {
        yield [new Dsn('foo', 'sns', self::USER, self::PASSWORD)];
    }

    public function incompleteDsnProvider(): iterable
    {
        yield [new Dsn('api', 'sns', self::USER)];

        yield [new Dsn('api', 'sns', null, self::PASSWORD)];
    }
}
