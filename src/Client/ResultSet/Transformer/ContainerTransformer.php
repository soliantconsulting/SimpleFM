<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Client\ResultSet\Transformer;

use Soliant\SimpleFM\Connection\ConnectionInterface;

final class ContainerTransformer
{
    /**
     * @var ConnectionInterface
     */
    private $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(string $value)
    {
        if ('' === $value) {
            return null;
        }

        return new StreamProxy($this->connection, $value);
    }
}
