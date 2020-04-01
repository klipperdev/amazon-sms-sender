<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bridge\SmsSender\Amazon\Transport;

use Aws\Credentials\Credentials;
use Aws\Exception\AwsException;
use Aws\Sns\SnsClient;
use Klipper\Bridge\SmsSender\Amazon\Mime\Header\SenderId;
use Klipper\Bridge\SmsSender\Amazon\Mime\Header\SmsType;
use Klipper\Bridge\SmsSender\Amazon\Mime\Header\UnstructuredHeader;
use Klipper\Component\SmsSender\Mime\Sms;
use Klipper\Component\SmsSender\SmsEnvelope;
use Klipper\Component\SmsSender\Transport\AbstractApiTransport;
use Klipper\Component\SmsSender\Transport\ErrorResult;
use Klipper\Component\SmsSender\Transport\Result;
use Klipper\Component\SmsSender\Transport\SuccessResult;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SnsTransport extends AbstractApiTransport
{
    /**
     * @var null|string
     */
    private $senderId;

    /**
     * @var null|string
     */
    private $type;

    /**
     * @var SnsClient
     */
    private $sns;

    /**
     * Constructor.
     *
     * @param string                        $accessKey  The aws access key
     * @param string                        $secretKey  The aws secret key
     * @param null|string                   $region     The aws region
     * @param null|string                   $senderId   The default sender id
     * @param null|string                   $type       The default message type ('Promotional' or 'Transactional')
     * @param null|EventDispatcherInterface $dispatcher The event dispatcher
     * @param null|HttpClientInterface      $client     The custom http client
     * @param null|LoggerInterface          $logger     The logger
     *
     * @throws
     */
    public function __construct(
        string $accessKey,
        string $secretKey,
        ?string $region = null,
        ?string $senderId = null,
        ?string $type = null,
        ?EventDispatcherInterface $dispatcher = null,
        ?HttpClientInterface $client = null,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($client, $dispatcher, $logger);

        $this->senderId = $senderId;
        $this->type = $type;
        $this->sns = new SnsClient([
            'version' => 'latest',
            'credentials' => new Credentials($accessKey, $secretKey),
            'region' => $region ?: 'eu-west-1',
            'handler' => null !== $this->client ? new HttpClientHandler($this->client) : null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return sprintf(
            'api://%s@sns?region=%s',
            $this->sns->getCredentials()->wait()->getAccessKeyId(),
            $this->sns->getRegion()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function hasRequiredFrom(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doSendSms(Sms $sms, SmsEnvelope $envelope, Result $result): void
    {
        $this->addDefaultAttributes($sms);

        foreach ($envelope->getRecipients() as $recipient) {
            try {
                $res = $this->sns->publish([
                    'MessageAttributes' => $this->buildAttributes($sms),
                    'Message' => (string) $sms->getText(),
                    'PhoneNumber' => $recipient->toString(),
                ]);

                $result->add(new SuccessResult($recipient, $res->toArray()));
            } catch (AwsException $e) {
                $error = null !== $e->getResult() ? $e->getResult()->toArray() : [];
                $result->add(new ErrorResult($recipient, $e->getAwsErrorMessage(), $e->getAwsErrorCode(), $error, $e));
            }
        }
    }

    /**
     * Add the default attributes in the message.
     *
     * @param Sms $sms The SMS message
     */
    protected function addDefaultAttributes(Sms $sms): void
    {
        $headers = $sms->getHeaders();

        if (null !== $this->senderId && !$headers->has(SenderId::NAME)) {
            $headers->add(new SenderId($this->senderId));
        }

        if (null !== $this->type && !$headers->has(SmsType::NAME)) {
            $headers->add(new SmsType($this->type));
        }
    }

    /**
     * Build the attributes for the request body.
     *
     * @param Sms $sms The SMS message
     */
    protected function buildAttributes(Sms $sms): array
    {
        $attr = [];

        foreach ($sms->getHeaders()->all() as $header) {
            if ($header instanceof UnstructuredHeader) {
                $attr[$header->getName()] = [
                    'DataType' => $header->getDataType(),
                    'StringValue' => $header->getValue(),
                ];
            }
        }

        return $attr;
    }
}
