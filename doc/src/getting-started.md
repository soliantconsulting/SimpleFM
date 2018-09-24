# Creating a connection

The first thing you want to do is to create a connection which can be used with the client. You will also need an
instance of an HTTP client implementing the
[`HttpClient` interface](https://github.com/php-http/httplug/blob/master/src/HttpClient.php). There are many different
implementations already created, so you can choose [one from php-http](https://github.com/php-http). For the sake of
simplicity, we are going with the CURL client here.

```php
<?php
use Http\Client\Curl\Client;
use Soliant\SimpleFM\Client\Connection;

$connection = new Connection(
    'https://my.filemaker.server',
    'username',
    'password',
    'sample-database',
    new DateTimeZone('FileMaker Server Timezone')
);
```

# Creating a REST client

With the connection and an HTTP client in place, you are now able to create a REST client: 

```php
<?php
use Soliant\SimpleFM\Client\RestClient;

$client = new RestClient($httpClient, $connection);
```

# Running your first query

To retrieve data from your FileMaker Server, you can use the REST client to modify and retrieve records from it. Data
will be returned as-is, so no conversion will be applied to any of the data. 

```php
<?php
$records = $client->findAll('sample-layout');

foreach ($records as $record) {
    // Do something with the record.
}
```

