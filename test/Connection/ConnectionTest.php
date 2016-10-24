<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Connection;

use Assert\InvalidArgumentException;
use Http\Client\HttpClient;
use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Soliant\SimpleFM\Authentication\Identity;
use Soliant\SimpleFM\Authentication\IdentityHandlerInterface;
use Soliant\SimpleFM\Connection\Command;
use Soliant\SimpleFM\Connection\Connection;
use Soliant\SimpleFM\Connection\Exception\InvalidResponseException;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\Uri;

final class ConnectionTest extends TestCase
{
    public function testNonSuccessResponse()
    {
        $httpClient = $this->prophesize(HttpClient::class);
        $httpClient->sendRequest(Argument::any())->willReturn(new EmptyResponse(204));

        $connection = new Connection(
            $httpClient->reveal(),
            new Uri(),
            'foo'
        );

        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionMessage('The FileMaker server responded with an unexpected error code: 204 No Content');
        $connection->execute(new Command('', []), '');
    }

    public function testNonXmlResponse()
    {
        $httpClient = $this->prophesize(HttpClient::class);
        $httpClient->sendRequest(Argument::any())->willReturn(new TextResponse('foo'));

        $connection = new Connection(
            $httpClient->reveal(),
            new Uri(),
            'foo'
        );

        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionMessage('An unexpected XML error occured');
        $connection->execute(new Command('', []), '');
    }

    public function testRequestWithoutCredentials()
    {
        $connection = new Connection(
            $this->createAssertiveHttpClient(function (RequestInterface $request) : ResponseInterface {
                $this->assertSame([
                    'Host' => ['example.com'],
                    'User-agent' => ['SimpleFM'],
                    'Content-type' => ['application/x-www-form-urlencoded'],
                    'Content-length' => ['12'],
                ], $request->getHeaders());

                return new TextResponse('<xml/>');
            }),
            new Uri('http://example.com'),
            'foo'
        );

        $connection->execute(new Command('', []), '/foo');
    }

    public function testRequestWithUriCredentials()
    {
        $connection = new Connection(
            $this->createAssertiveHttpClient(function (RequestInterface $request) : ResponseInterface {
                $this->assertSame(['Basic Zm9vJTpiYXIl'], $request->getHeader('Authorization'));

                return new TextResponse('<xml/>');
            }),
            new Uri('http://foo%25:bar%25@example.com'),
            'foo'
        );

        $connection->execute(new Command('', []), '/foo');
    }

    public function testRequestWithUriAndCommandCredentials()
    {
        $identityHandler = $this->prophesize(IdentityHandlerInterface::class);
        $identityHandler->decryptPassword(Argument::any())->willReturn('bat2');

        $connection = new Connection(
            $this->createAssertiveHttpClient(function (RequestInterface $request) : ResponseInterface {
                $this->assertSame(['Basic YmF6OmJhdDI='], $request->getHeader('Authorization'));

                return new TextResponse('<xml/>');
            }),
            new Uri('http://foo%25:bar%25@example.com'),
            'foo',
            $identityHandler->reveal()
        );

        $connection->execute((new Command('', []))->withIdentity(new Identity('baz', 'bat')), '/foo');
    }

    public function testRequestWithCommandIdentity()
    {
        $identityHandler = $this->prophesize(IdentityHandlerInterface::class);
        $identityHandler->decryptPassword(Argument::any())->willReturn('bat2');

        $connection = new Connection(
            $this->createAssertiveHttpClient(function (RequestInterface $request) : ResponseInterface {
                $this->assertSame(['Basic YmF6OmJhdDI='], $request->getHeader('Authorization'));

                return new TextResponse('<xml/>');
            }),
            new Uri('http://example.com'),
            'foo',
            $identityHandler->reveal()
        );

        $connection->execute((new Command('', []))->withIdentity(new Identity('baz', 'bat')), '/foo');
    }

    public function testRequestWithCommandIdentityWithoutIdentityHandler()
    {
        $connection = new Connection(
            $this->prophesize(HttpClient::class)->reveal(),
            new Uri('http://example.com'),
            'foo'
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('identity handler must be set');
        $connection->execute((new Command('', []))->withIdentity(new Identity('baz', 'bat')), '/foo');
    }

    public function testGetAssetWithNonSuccessResponse()
    {
        $httpClient = $this->prophesize(HttpClient::class);
        $httpClient->sendRequest(Argument::any())->willReturn(new EmptyResponse(204));

        $connection = new Connection(
            $httpClient->reveal(),
            new Uri(),
            'foo'
        );

        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionMessage('The FileMaker server responded with an unexpected error code: 204 No Content');
        $connection->getAsset('/foo');
    }

    public function testGetAssetWithUriCredentials()
    {
        $connection = new Connection(
            $this->createAssertiveHttpClient(function (RequestInterface $request) : ResponseInterface {
                $this->assertSame(['Basic Zm9vJTpiYXIl'], $request->getHeader('Authorization'));

                return new TextResponse('bar');
            }),
            new Uri('http://foo%25:bar%25@example.com'),
            'foo'
        );

        $connection->getAsset('/foo');
    }

    public function testGetAssetWithQueryParameters()
    {
        $connection = new Connection(
            $this->createAssertiveHttpClient(function (RequestInterface $request) : ResponseInterface {
                $this->assertSame('http://example.com/foo?bar=baz', (string) $request->getUri());

                return new TextResponse('bar');
            }),
            new Uri('http://example.com'),
            'foo'
        );

        $connection->getAsset('/foo?bar=baz');
    }

    public function testRequestLogging()
    {
        $httpClient = $this->prophesize(HttpClient::class);
        $httpClient->sendRequest(Argument::any())->willReturn(new TextResponse('<foo/>'));

        $logger = $this->prophesize(LoggerInterface::class);
        $logger->info('https://example.com/grammar.xml?-db=foo&-lay')->shouldBeCalled();

        $connection = new Connection(
            $httpClient->reveal(),
            new Uri('https://example.com'),
            'foo',
            null,
            $logger->reveal()
        );

        $connection->execute(new Command('', []), '/grammar.xml');
    }

    private function createAssertiveHttpClient(callable $assertion) : HttpClient
    {
        $httpClient = $this->prophesize(HttpClient::class);
        $httpClient->sendRequest(Argument::any())->will(function (
            array $parameters
        ) use ($assertion) : ResponseInterface {
            return $assertion($parameters[0]);
        });

        return $httpClient->reveal();
    }
}
