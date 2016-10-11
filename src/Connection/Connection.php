<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Connection;

use Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SimpleXMLElement;
use Soliant\SimpleFM\Connection\Exception\InvalidResponseException;
use Zend\Diactoros\Request;
use Zend\Diactoros\Stream;

final class Connection implements ConnectionInterface
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var UriInterface
     */
    private $uri;

    /**
     * @var string
     */
    private $database;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        HttpClient $httpClient,
        UriInterface $uri,
        string $database,
        LoggerInterface $logger = null
    ) {
        $this->httpClient = $httpClient;
        $this->uri = $uri;
        $this->database = $database;
        $this->logger = $logger ?: new NullLogger();
    }

    public function execute(Command $command, string $grammarPath) : SimpleXMLElement
    {
        $uri = $this->uri->withPath($grammarPath);
        $response = $this->httpClient->sendRequest($this->buildRequest($command, $uri));

        if (200 !== (int) $response->getStatusCode()) {
            throw InvalidResponseException::fromUnsuccessfulResponse($response);
        }

        $previousValue = libxml_use_internal_errors(true);
        $xml = simplexml_load_string((string) $response->getBody());
        libxml_use_internal_errors($previousValue);

        if (false === $xml) {
            throw InvalidResponseException::fromXmlError(libxml_get_last_error());
        }

        return $xml;
    }

    private function buildRequest(Command $command, UriInterface $uri) : RequestInterface
    {
        $parameters = sprintf('-db=%s&%s', urlencode($this->database), $command);

        $body = new Stream('php://temp', 'wb+');
        $body->write($parameters);
        $body->rewind();

        $request = (new Request($uri->withUserInfo(''), 'POST'))
            ->withAddedHeader('User-agent', 'SimpleFM')
            ->withAddedHeader('Content-type', 'application/x-www-form-urlencoded')
            ->withAddedHeader('Content-length', (string) strlen($parameters))
            ->withBody($body);

        $credentials = urldecode($uri->getUserInfo());

        if ($command->hasCredentials()) {
            $credentials = sprintf('%s:%s', $command->getUsername(), $command->getPassword());
        }

        $this->logger->info(sprintf('%s?%s', (string) $uri->withUserInfo(''), $parameters));

        if ('' === $credentials) {
            return $request;
        }

        return $request->withAddedHeader('Authorization', sprintf('Basic %s', base64_encode($credentials)));
    }
}
