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
class SenderId extends UnstructuredHeader
{
    public const NAME = 'AWS.SNS.SMS.SenderID';

    public function __construct(string $value)
    {
        parent::__construct(static::NAME, substr($value, 0, 11));
    }
}
