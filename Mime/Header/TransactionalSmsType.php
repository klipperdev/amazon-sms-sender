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
class TransactionalSmsType extends SmsType
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(static::TYPE_TRANSACTIONAL);
    }
}
