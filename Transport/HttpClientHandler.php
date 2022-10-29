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

use Aws\CommandInterface;
use Aws\Exception\AwsException;
use Aws\Result;
use GuzzleHttp\Promise;
use Psr\Http\Message\RequestInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class HttpClientHandler
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Call the handler.
     *
     * @param CommandInterface $cmd     The aws command
     * @param RequestInterface $request The aws request
     *
     * @return \Exception|Promise\PromiseInterface
     *
     * @throws
     */
    public function __invoke(CommandInterface $cmd, RequestInterface $request)
    {
        $uri = (string) $request->getUri();

        $response = $this->client->request($request->getMethod(), $uri, [
            'headers' => $request->getHeaders(),
            'body' => $request->getBody()->getContents(),
        ]);

        if (200 !== $resStatusCode = $response->getStatusCode()) {
            $data = $this->prepareErrorContext($request, $response);

            throw new AwsException($data['message'], $cmd, $data);
        }

        $data = new \SimpleXMLElement($response->getContent(false));

        return Promise\promise_for(new Result([
            'MessageId' => (string) $data->PublishResult->MessageId,
            '@metadata' => [
                'statusCode' => $resStatusCode,
                'effectiveUri' => $uri,
                'headers' => $this->prepareResponseHeaders($response),
            ],
        ]));
    }

    /**
     * Prepare the headers og the http client response.
     *
     * @param ResponseInterface $response The http client response
     *
     * @throws
     */
    private function prepareResponseHeaders(ResponseInterface $response): array
    {
        $resHeaders = [];

        foreach ($response->getHeaders(false) as $header => $value) {
            $resHeaders[$header] = \is_array($value) ? implode(', ', $value) : (string) $value;
        }

        return $resHeaders;
    }

    /**
     * Prepare the error context.
     *
     * @param RequestInterface  $request  The http client request
     * @param ResponseInterface $response The http client response
     *
     * @throws
     */
    private function prepareErrorContext(RequestInterface $request, ResponseInterface $response): array
    {
        $error = new \SimpleXMLElement($response->getContent(false));

        return [
            'response' => $response,
            'request' => $request,
            'request_id' => (string) $error->RequestId,
            'type' => (string) $error->Error->Type,
            'code' => (string) $error->Error->Code,
            'message' => (string) $error->Error->Message,
            'connection_error' => false,
        ];
    }
}
