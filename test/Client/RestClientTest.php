<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Client;

use DateTimeZone;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;
use Soliant\SimpleFM\Client\Connection;
use Soliant\SimpleFM\Client\Exception\FileMakerException;
use Soliant\SimpleFM\Client\RestClient;
use Soliant\SimpleFM\Query\Conditions;
use Soliant\SimpleFM\Query\Field;
use Soliant\SimpleFM\Query\Query;
use Soliant\SimpleFM\Sort\Sort;
use Zend\Diactoros\Response;

final class RestClientTest extends TestCase
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var array
     */
    private $defaultData;

    protected function setUp()
    {
        $this->connection = new Connection('http://foo', 'bar', 'baz', 'bat', new DateTimeZone('Europe/Berlin'));
        $this->httpClient = new Client();
        $this->defaultData = ['string' => 'a', 'int' => 1, 'float' => 5.5];
    }

    public function testGetConnection() : void
    {
        $client = new RestClient($this->httpClient, $this->connection);
        $this->assertSame($this->connection, $client->getConnection());
    }

    public function testCreateRecord() : void
    {
        $this->addAuthResponse();
        $this->addJsonResponse(['recordId' => 1, 'modId' => 1]);

        $client = new RestClient($this->httpClient, $this->connection);
        $result = $client->createRecord('a', $this->defaultData);

        $this->assertSame(['recordId' => 1, 'modId' => 1], $result);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertSame('Bearer auth-token', $lastRequest->getHeaderLine('Authorization'));
        $this->assertSame(
            'http://foo/fmi/data/v1/databases/bat/layouts/a/records',
            (string) $lastRequest->getUri()
        );
        $this->assertSame('POST', $lastRequest->getMethod());
        $this->assertSame([
            'fieldData' => $this->defaultData,
        ], json_decode((string) $lastRequest->getBody(), true));
    }

    public function testUpdateRecord() : void
    {
        $this->addAuthResponse();
        $this->addJsonResponse(['modId' => 2]);

        $client = new RestClient($this->httpClient, $this->connection);
        $result = $client->updateRecord('a', 1, $this->defaultData);

        $this->assertSame(['modId' => 2], $result);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertSame('Bearer auth-token', $lastRequest->getHeaderLine('Authorization'));
        $this->assertSame(
            'http://foo/fmi/data/v1/databases/bat/layouts/a/records/1',
            (string) $lastRequest->getUri()
        );
        $this->assertSame('PATCH', $lastRequest->getMethod());
        $this->assertSame([
            'fieldData' => $this->defaultData,
        ], json_decode((string) $lastRequest->getBody(), true));
    }

    public function testDeleteRecord() : void
    {
        $this->addAuthResponse();
        $this->addJsonResponse(['modId' => 2]);

        $client = new RestClient($this->httpClient, $this->connection);
        $client->deleteRecord('a', 1);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertSame('Bearer auth-token', $lastRequest->getHeaderLine('Authorization'));
        $this->assertSame(
            'http://foo/fmi/data/v1/databases/bat/layouts/a/records/1',
            (string) $lastRequest->getUri()
        );
        $this->assertSame('DELETE', $lastRequest->getMethod());
        $this->assertSame([], json_decode((string) $lastRequest->getBody(), true));
    }

    public function testGetRecord() : void
    {
        $this->addAuthResponse();
        $this->addJsonResponse([
            'data' => [
                ['fieldData' => $this->defaultData]
            ],
        ]);

        $client = new RestClient($this->httpClient, $this->connection);
        $result = $client->getRecord('a', 1);

        $this->assertSame(['fieldData' => $this->defaultData], $result);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertSame('Bearer auth-token', $lastRequest->getHeaderLine('Authorization'));
        $this->assertSame(
            'http://foo/fmi/data/v1/databases/bat/layouts/a/records/1',
            (string) $lastRequest->getUri()
        );
        $this->assertSame('GET', $lastRequest->getMethod());
        $this->assertSame('', (string) $lastRequest->getBody());
    }

    public function testUploadContainerData() : void
    {
        $this->addAuthResponse();
        $this->addJsonResponse([]);

        $client = new RestClient($this->httpClient, $this->connection);
        $client->uploadContainerData('a', 1, 'foobar', 'foo', 2);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertSame('Bearer auth-token', $lastRequest->getHeaderLine('Authorization'));
        $this->assertSame(
            'http://foo/fmi/data/v1/databases/bat/layouts/a/records/1/containers/foo/2',
            (string) $lastRequest->getUri()
        );
        $this->assertSame('POST', $lastRequest->getMethod());
        $this->assertSame('multipart/form-data; boundary=upload-boundary', $lastRequest->getHeaderLine('Content-Type'));
        $this->assertSame(
            "--upload-boundary\nContent-Disposition: file; name=\"upload\"\n\nfoobar",
            (string) $lastRequest->getBody()
        );
    }

    public function testGetContainerData() : void
    {
        $response = new Response('php://memory', 200, [
            'Content-Type' => 'text/plain',
        ]);
        $response->getBody()->write('foobar');
        $this->httpClient->addResponse($response);

        $client = new RestClient($this->httpClient, $this->connection);
        $result = $client->getContainerData('http://foo/bar');

        $this->assertSame('foobar', (string) $result);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertFalse($lastRequest->hasHeader('Authorization'));
        $this->assertSame('http://foo/bar', (string) $lastRequest->getUri());
        $this->assertSame('GET', $lastRequest->getMethod());
        $this->assertSame('', (string) $lastRequest->getBody());
    }

    public function testFindWithoutQueryAndWithoutParameters() : void
    {
        $this->addAuthResponse();
        $this->addJsonResponse([
            'data' => [
                ['fieldData' => $this->defaultData]
            ],
        ]);

        $client = new RestClient($this->httpClient, $this->connection);
        $result = $client->find('a');

        $this->assertSame([['fieldData' => $this->defaultData]], $result);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertSame('Bearer auth-token', $lastRequest->getHeaderLine('Authorization'));
        $this->assertSame(
            'http://foo/fmi/data/v1/databases/bat/layouts/a/records',
            (string) $lastRequest->getUri()
        );
        $this->assertSame('GET', $lastRequest->getMethod());
        $this->assertSame('', (string) $lastRequest->getBody());
    }

    public function testFindWithoutQueryAndWithParameters() : void
    {
        $this->addAuthResponse();
        $this->addJsonResponse([
            'data' => [
                ['fieldData' => $this->defaultData]
            ],
        ]);

        $client = new RestClient($this->httpClient, $this->connection);
        $result = $client->find('a', null, 0, 1, new Sort('foo', true), new Sort('bar', false));

        $this->assertSame([['fieldData' => $this->defaultData]], $result);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertSame('Bearer auth-token', $lastRequest->getHeaderLine('Authorization'));
        $uri = explode('?', (string) $lastRequest->getUri());
        parse_str($uri[1], $query);
        $this->assertSame(
            'http://foo/fmi/data/v1/databases/bat/layouts/a/records',
            $uri[0]
        );
        $this->assertSame(
            [
                '_offset' => '0',
                '_limit' => '1',
                '_sort' => '[{"fieldName":"foo","sortOrder":"ascend"},{"fieldName":"bar","sortOrder":"descend"}]',
            ],
            $query
        );
        $this->assertSame('GET', $lastRequest->getMethod());
        $this->assertSame('', (string) $lastRequest->getBody());
    }

    public function testFindWithQueryAndWithoutParameters() : void
    {
        $this->addAuthResponse();
        $this->addJsonResponse([
            'data' => [
                ['fieldData' => $this->defaultData]
            ],
        ]);

        $query = new Query(
            new Conditions(false, new Field('foo', '1'), new Field('bar', '2')),
            new Conditions(true, new Field('foo', '3'), new Field('bar', '4'))
        );

        $client = new RestClient($this->httpClient, $this->connection);
        $result = $client->find('a', $query);

        $this->assertSame([['fieldData' => $this->defaultData]], $result);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertSame('Bearer auth-token', $lastRequest->getHeaderLine('Authorization'));
        $this->assertSame(
            'http://foo/fmi/data/v1/databases/bat/layouts/a/_find',
            (string) $lastRequest->getUri()
        );
        $this->assertSame('POST', $lastRequest->getMethod());
        $this->assertSame([
            'query' => [
                ['foo' => '1', 'bar' => '2'],
                ['omit' => true, 'foo' => '3', 'bar' => '4'],
            ],
        ], json_decode((string) $lastRequest->getBody(), true));
    }

    public function testFindWithQueryAndWithParameters() : void
    {
        $this->addAuthResponse();
        $this->addJsonResponse([
            'data' => [
                ['fieldData' => $this->defaultData]
            ],
        ]);

        $query = new Query(
            new Conditions(false, new Field('foo', '1'), new Field('bar', '2')),
            new Conditions(true, new Field('foo', '3'), new Field('bar', '4'))
        );

        $client = new RestClient($this->httpClient, $this->connection);
        $result = $client->find('a', $query, 0, 1, new Sort('foo', true), new Sort('bar', false));

        $this->assertSame([['fieldData' => $this->defaultData]], $result);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertSame('Bearer auth-token', $lastRequest->getHeaderLine('Authorization'));
        $this->assertSame(
            'http://foo/fmi/data/v1/databases/bat/layouts/a/_find',
            (string) $lastRequest->getUri()
        );
        $this->assertSame('POST', $lastRequest->getMethod());
        $this->assertSame([
            'query' => [
                ['foo' => '1', 'bar' => '2'],
                ['omit' => true, 'foo' => '3', 'bar' => '4'],
            ],
            'offset' => 0,
            'limit' => 1,
            'sort' => [
                ['fieldName' => 'foo', 'sortOrder' => 'ascend'],
                ['fieldName' => 'bar', 'sortOrder' => 'descend'],
            ],
        ], json_decode((string) $lastRequest->getBody(), true));
    }

    public function testCompleteLayoutXml()
    {
        $response = new Response('php://temp', 200, [
            'Content-Type' => 'text/xml',
        ]);
        $response->getBody()->write(file_get_contents(__DIR__ . '/TestAssets/sample_fmpxmllayout.xml'));
        $this->httpClient->addResponse($response);

        $client = new RestClient($this->httpClient, $this->connection);
        $layout = $client->getLayout('foo');

        $this->assertSame('FMServer_Sample', $layout->getDatabase());
        $this->assertSame('Projects | Web', $layout->getName());
        $this->assertCount(9, $layout->getFields());

        $this->assertTrue($layout->hasField('Projects::Project Name'));
        $nameField = $layout->getField('Projects::Project Name');
        $this->assertFalse($nameField->hasValueList());
        $this->assertSame('EDITTEXT', $nameField->getType());

        $this->assertTrue($layout->hasField('Projects::Status on Screen'));
        $statusField = $layout->getField('Projects::Status on Screen');
        $this->assertTrue($statusField->hasValueList());
        $this->assertSame('Status', (string) $statusField->getValueList());
        $this->assertSame('Status', $statusField->getValueList()->getName());
        $this->assertSame('Completed', (string) $statusField->getValueList()->getValues()[0]);
        $this->assertSame('Completed', $statusField->getValueList()->getValues()[0]->getDisplay());
        $this->assertSame('Completed', $statusField->getValueList()->getValues()[0]->getValue());

        $this->assertNull($layout->getField('non-existent'));

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertSame('Basic YmFyOmJheg==', $lastRequest->getHeaderLine('Authorization'));
        $this->assertSame(
            'http://foo/fmi/xml/FMPXMLLAYOUT.xml?db=bat&lay=foo-view',
            (string) $lastRequest->getUri()
        );
        $this->assertSame('GET', $lastRequest->getMethod());
        $this->assertSame('', (string) $lastRequest->getBody());
    }

    public function testErrorLayoutXml()
    {
        $response = new Response('php://temp', 200, [
            'Content-Type' => 'text/xml',
        ]);
        $response->getBody()->write(file_get_contents(__DIR__ . '/TestAssets/error_fmpxmllayout.xml'));
        $this->httpClient->addResponse($response);

        $client = new RestClient($this->httpClient, $this->connection);

        $this->expectException(FileMakerException::class);
        $this->expectExceptionMessage('The XML api returned with an error code of 1');
        $client->getLayout('foo');
    }

    private function addAuthResponse() : void
    {
        $response = new Response('php://memory', 200, [
            'Content-Type' => 'application/json; charset=utf-8',
            'X-FM-Data-Access-Token' => 'auth-token',
        ]);
        $response->getBody()->write(json_encode([
            'response' => [
                'token' => 'auth-token',
            ],
            'messages' => [
                [
                    'code' => '0',
                    'message' => 'OK',
                ],
            ],
        ]));

        $this->httpClient->addResponse($response);
    }

    private function addJsonResponse(array $data, ?array $messages = null) : void
    {
        $json = [
            'response' => $data,
            'messages' => $messages ?? [
                ['code' => '0', 'message' => 'OK']
            ],
        ];

        $response = new Response('php://memory', 200, [
            'Content-Type' => 'application/json; charset=utf-8',
        ]);
        $response->getBody()->write(json_encode($json));

        $this->httpClient->addResponse($response);
    }
}
