<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bridge\SmsSender\Amazon\Mime\Header;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MaxPrice extends UnstructuredHeader
{
    public const NAME = 'AWS.SNS.SMS.MaxPrice';

    public function __construct(string $value)
    {
        parent::__construct(static::NAME, $value);
    }

    public function getDataType(): string
    {
        return 'Number';
    }
}
