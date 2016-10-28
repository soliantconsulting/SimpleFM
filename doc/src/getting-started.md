# Creating a connection

The first thing you want to do is to create a connection which can be used with either the layout client or the
result set client. While you can always implement your own connection class by implementing the `ConnectionInterface`,
most of the time the built-in one should be sufficient.

To create an instance of a connection, you need an instance of an HTTP client implementing the
[`HttpClient` interface](https://github.com/php-http/httplug/blob/master/src/HttpClient.php). There are many different
implementations already created, so you can choose [one from php-http](https://github.com/php-http). For the sake of
simplicity, we are going with the CURL client here.

The next thing you need is a PSR-7 URI object. Again, there are many different implementations for that out in the
world, and we will use the one shipped with `Zend\Diactoros`. The URI object should contain the scheme, host and
optionally a port and an authority part.

```php
<?php
use Http\Client\Curl\Client;
use Soliant\SimpleFM\Connection\Connection;
use Zend\Diactoros\Uri;

$connection = new Connection(
    new Client(),
    new Uri('https://username:password@my.filemaker.server'),
    'sample-database'
);
```

# Creating a result set client

With the connection in place, you are now able to create a result set client. Beside the connection, the result set
client also requires a `DateTimeZone` object to be able to interpret the local times it receives from the file maker
server correctly. This one should be set to the time zone your server operates on.

```php
<?php
use Soliant\SimpleFM\Client\ResultSet\ResultSetClient;

$resultSetClient = new ResultSetClient($connection, new DateTimeZone('UTC'));
```

# Running your first query

To retrieve data from your file maker server, you can use the result set client to execute abitrary commands. It will
automatically populate an array with the records it receives based on the meta data the file maker server returns with
every response. This means that any date or time based field is cast to a `DateTimeImmutable` object, any number is
cast to a `Decimal` object and file containers are cast to a `StreamInterface`.

```php
<?php
use Soliant\SimpleFM\Connection\Command;

$records = $resultSetClient->execute(new Command('sample-layout', ['-findall' => null]));

foreach ($records as $record) {
    // Do something with the record.
}
```

