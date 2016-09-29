<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Connection;

use Http\Client\HttpClient;
use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
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
        $connection = new Connection(
            $this->createAssertiveHttpClient(function (RequestInterface $request) : ResponseInterface {
                $this->assertSame(['Basic YmF6OmJhdA=='], $request->getHeader('Authorization'));

                return new TextResponse('<xml/>');
            }),
            new Uri('http://foo%25:bar%25@example.com'),
            'foo'
        );

        $connection->execute((new Command('', []))->withCredentials('baz', 'bat'), '/foo');
    }

    public function testRequestWithCommandCredentials()
    {
        $connection = new Connection(
            $this->createAssertiveHttpClient(function (RequestInterface $request) : ResponseInterface {
                $this->assertSame(['Basic YmF6OmJhdA=='], $request->getHeader('Authorization'));

                return new TextResponse('<xml/>');
            }),
            new Uri('http://example.com'),
            'foo'
        );

        $connection->execute((new Command('', []))->withCredentials('baz', 'bat'), '/foo');
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
