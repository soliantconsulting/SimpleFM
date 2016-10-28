# Debugging requests

Sometimes it can be really hard to debug a request without looking at the actual XML result directly. To make this task
a little easier, you can provide a PSR-3 compliant logger to the connection. This will result in each request URL being
logged, with the request parameters appended in the query.

Many frameworks and libraries provide loggers implementing PSR-3, so you have a wide variety to choose from. In the
following example we will use the PSR-3 adapter of `Zend\Log`:

```php
<?php
use Zend\Log\Logger;
use Zend\Log\PsrLoggerAdapter;
use Zend\Log\Writer\Stream;

$logger = new PsrLoggerAdapter(
    new Logger([
        'writers' => [
            new Stream('/path/to/logfile')
        ],
    ])
);

$connection = new Connection(
    $httpClient,
    $uri,
    $databsae,
    null,
    $logger
);
```

!!!note
    Since a logger will cause I/O for every single request made against the file maker server, you should **not** enable
    this in production.
