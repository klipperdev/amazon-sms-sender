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

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class HttpClientContents
{
    public static function getSuccessResponse(): string
    {
        return <<<'EOF'
            <PublishResponse xmlns="http://sns.amazonaws.com/doc/2010-03-31/">
              <PublishResult>
                <MessageId>f83ee12e-58b2-5b64-b733-b4aac5bcf80e</MessageId>
              </PublishResult>
              <ResponseMetadata>
                <RequestId>67f2c6ef-9f73-5c42-a8fa-936b027d6f28</RequestId>
              </ResponseMetadata>
            </PublishResponse>
            EOF
        ;
    }

    public static function getErrorResponse(?string $message = null, ?string $code = null): string
    {
        $message = $message ?? 'The request signature we calculated does not match the signature you provided.';
        $code = $code ?? 'SignatureDoesNotMatch';

        return <<<EOT
            <ErrorResponse xmlns="http://sns.amazonaws.com/doc/2010-03-31/">
              <Error>
                <Type>Sender</Type>
                <Code>{$code}</Code>
                <Message>{$message}</Message>
              </Error>
              <RequestId>809accec-3ab7-53bd-808b-9968a11074f1</RequestId>
            </ErrorResponse>
            EOT
        ;
    }
}
