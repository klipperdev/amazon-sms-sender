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
class SmsType extends UnstructuredHeader
{
    public const NAME = 'AWS.SNS.SMS.SMSType';

    public const TYPE_TRANSACTIONAL = 'Transactional';

    public const TYPE_PROMOTIONAL = 'Promotional';

    /**
     * Constructor.
     *
     * @param string $value The SMS type
     */
    public function __construct(string $value)
    {
        $value = static::TYPE_TRANSACTIONAL === $value ? $value : static::TYPE_PROMOTIONAL;

        parent::__construct(static::NAME, $value);
    }
}
