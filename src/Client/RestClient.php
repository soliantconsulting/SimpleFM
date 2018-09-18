<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Client;

use Http\Client\HttpClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Soliant\SimpleFM\Client\Exception\FileMakerException;
use Soliant\SimpleFM\Query\Conditions;
use Soliant\SimpleFM\Query\Query;
use Soliant\SimpleFM\Sort\Sort;
use Zend\Diactoros\Request;

final class RestClient implements ClientInterface
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string|null
     */
    private $accessToken;

    public function __construct(HttpClient $httpClient, Connection $connection)
    {
        $this->httpClient = $httpClient;
        $this->connection = $connection;
    }

    public function getConnection() : Connection
    {
        return $this->connection;
    }

    public function createRecord(string $layout, array $data) : array
    {
        return $this->request(sprintf('layouts/%s/records', $layout), 'POST', [
            'fieldData' => $data
        ]);
    }

    public function updateRecord(string $layout, int $recordId, array $data) : array
    {
        return $this->request(sprintf('layouts/%s/records/%d', $layout, $recordId), 'PATCH', [
            'fieldData' => $data
        ]);
    }

    public function deleteRecord(string $layout, int $recordId) : void
    {
        $this->request(sprintf('layouts/%s/records/%d', $layout, $recordId), 'DELETE');
    }

    public function getRecord(string $layout, int $recordId) : array
    {
        return $this->request(sprintf('layouts/%s/records/%d', $layout, $recordId), 'GET')['data'][0];
    }

    public function uploadContainerData(
        string $layout,
        int $recordId,
        string $data,
        string $fieldName,
        int $fieldRepetition = 0
    ) : void {
        $url = sprintf(
            '%s/fmi/data/v1/databases/%s/layouts/%s/records/%d/containers/%s/%d',
            $this->connection->getBaseUri(),
            $this->connection->getDatabase(),
            $layout,
            $recordId,
            $fieldName,
            $fieldRepetition
        );

        $headers = [
            'Authorization' => sprintf('Bearer %s', $this->getAccessToken()),
            'Content-Type' => 'multipart/form-data; boundary=upload-boundary',
        ];

        $request = new Request($url, 'POST', 'php://temp', $headers);
        $request->getBody()->write(sprintf(
            "--upload-boundary\n%s\n\n%s",
            'Content-Disposition: file; name="upload"',
            $data
        ));

        $this->decodeResponse($this->httpClient->sendRequest($request));
    }

    public function getContainerData(string $url) : StreamInterface
    {
        $request = new Request($url, 'GET');
        return $this->httpClient->sendRequest($request)->getBody();
    }

    public function find(
        string $layout,
        ?Query $query = null,
        ?int $offset = null,
        ?int $limit = null,
        Sort ...$sorts
    ) : array {
        if (null === $query) {
            return $this->findRange($layout, $offset, $limit, ...$sorts);
        }

        $queryData = [];

        $queryData['query'] = array_map(function (Conditions $conditions) : array {
            $result = [];

            if ($conditions->isOmit()) {
                $result['omit'] = true;
            }

            foreach ($conditions->getFields() as $field) {
                $result[$field->getName()] = $field->getValue();
            }

            return $result;
        }, $query->getOrConditions());

        if (null !== $offset) {
            $queryData['offset'] = $offset;
        }

        if (null !== $limit) {
            $queryData['limit'] = $limit;
        }

        if (! empty($sorts)) {
            $queryData['sort'] = $this->convertSort(...$sorts);
        }

        return $this->request(sprintf('layouts/%s/_find', $layout), 'POST', $queryData)['data'];
    }

    private function findRange(string $layout, ?int $offset, ?int $limit, Sort ...$sorts) : array
    {
        $queryData = [];

        if (null !== $offset) {
            $queryData['_offset'] = $offset;
        }

        if (null !== $limit) {
            $queryData['_limit'] = $limit;
        }

        if (! empty($sorts)) {
            $queryData['_sort'] = json_encode($this->convertSort(...$sorts));
        }

        return $this->request(sprintf('layouts/%s/records', $layout), 'GET', $queryData)['data'];
    }

    private function convertSort(Sort ...$sorts) : array
    {
        return array_map(
            function (Sort $sort) : array {
                return [
                    'fieldName' => $sort->getFieldName(),
                    'sortOrder' => $sort->isAscending() ? 'ascend' : 'descend',
                ];
            },
            $sorts
        );
    }

    private function request(string $path, string $method, ?array $data = null) : array
    {
        $url = sprintf(
            '%s/fmi/data/v1/databases/%s/%s',
            $this->connection->getBaseUri(),
            $this->connection->getDatabase(),
            $path
        );

        $headers = [
            'Authorization' => sprintf('Bearer %s', $this->getAccessToken()),
        ];

        if ($method !== 'GET') {
            $headers['Content-Type'] = 'application/json';
        } elseif (null !== $data) {
            $url .= '?' . http_build_query($data);
        }

        $request = new Request($url, $method, 'php://temp', $headers);

        if ($method !== 'GET') {
            $request->getBody()->write(json_encode($data ?? []));
        }

        return $this->decodeResponse($this->httpClient->sendRequest($request));
    }

    private function getAccessToken() : string
    {
        if (null !== $this->accessToken) {
            return $this->accessToken;
        }

        $request = new Request(
            sprintf(
                '%s/fmi/data/v1/databases/%s/sessions',
                $this->connection->getBaseUri(),
                $this->connection->getDatabase()
            ),
            'POST',
            'php://temp',
            [
                'Content-Type' => 'application/json',
                'Authorization' => sprintf('Basic %s', base64_encode(sprintf(
                    '%s:%s',
                    $this->connection->getUsername(),
                    $this->connection->getPassword()
                ))),
            ]
        );
        $request->getBody()->write('{}');
        $response = $this->httpClient->sendRequest($request);
        $this->decodeResponse($response);

        return $this->accessToken = $response->getHeaderLine('X-FM-Data-Access-Token');
    }

    private function decodeResponse(ResponseInterface $response) : array
    {
        $data = json_decode((string) $response->getBody(), true);

        if (null === $data || ! array_key_exists('messages', $data) || ! array_key_exists('response', $data)) {
            throw FileMakerException::fromUnknown();
        }

        if (200 !== $response->getStatusCode()) {
            throw FileMakerException::fromMessages($data['messages']);
        }

        if ('0' !== ($data['messages'][0]['code'] ?? null)) {
            throw FileMakerException::fromMessages($data['messages']);
        }

        return $data['response'];
    }
}
