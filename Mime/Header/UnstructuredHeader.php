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

use Symfony\Component\Mime\Header\UnstructuredHeader as BaseUnstructuredHeader;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class UnstructuredHeader extends BaseUnstructuredHeader
{
    /**
     * Get the data type.
     */
    public function getDataType(): string
    {
        return 'String';
    }
}
