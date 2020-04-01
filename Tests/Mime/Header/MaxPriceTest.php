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

use Klipper\Bridge\SmsSender\Amazon\Mime\Header\MaxPrice;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class MaxPriceTest extends TestCase
{
    public function testGetDataType(): void
    {
        $header = new MaxPrice('0.10');

        static::assertSame('Number', $header->getDataType());
    }

    public function testValue(): void
    {
        $header = new MaxPrice('0.10');

        static::assertSame('0.10', $header->getValue());
    }
}
