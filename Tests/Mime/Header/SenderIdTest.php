<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bridge\SmsSender\Amazon\Tests\Mime\Header;

use Klipper\Bridge\SmsSender\Amazon\Mime\Header\SenderId;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SenderIdTest extends TestCase
{
    public function testGetDataType(): void
    {
        $header = new SenderId('SENDER ID');

        static::assertSame('String', $header->getDataType());
    }

    public function testValue(): void
    {
        $header = new SenderId('SENDER ID');

        static::assertSame('SENDER ID', $header->getValue());
    }

    public function testValueWithLongId(): void
    {
        $header = new SenderId('SENDER ID LONG');

        static::assertSame('SENDER ID L', $header->getValue());
    }
}
