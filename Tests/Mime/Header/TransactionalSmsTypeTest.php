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

use Klipper\Bridge\SmsSender\Amazon\Mime\Header\TransactionalSmsType;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class TransactionalSmsTypeTest extends TestCase
{
    public function testGetDataType(): void
    {
        $header = new TransactionalSmsType();

        static::assertSame('String', $header->getDataType());
    }

    public function testTransactionalValue(): void
    {
        $header = new TransactionalSmsType();

        static::assertSame('Transactional', $header->getValue());
    }
}
