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

use Klipper\Bridge\SmsSender\Amazon\Mime\Header\SmsType;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SmsTypeTest extends TestCase
{
    public function testGetDataType(): void
    {
        $header = new SmsType('Transactional');

        static::assertSame('String', $header->getDataType());
    }

    public function testTransactionalValue(): void
    {
        $header = new SmsType('Transactional');

        static::assertSame('Transactional', $header->getValue());
    }

    public function testPromotionalValue(): void
    {
        $header = new SmsType('Promotional');

        static::assertSame('Promotional', $header->getValue());
    }

    public function testValueWithInvalidType(): void
    {
        $header = new SmsType('Invalid');

        static::assertSame('Promotional', $header->getValue());
    }
}
