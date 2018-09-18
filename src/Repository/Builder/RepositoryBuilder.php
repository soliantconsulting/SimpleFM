<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder;

use Soliant\SimpleFM\Client\ClientInterface;
use Soliant\SimpleFM\Repository\Builder\Metadata\MetadataBuilderInterface;
use Soliant\SimpleFM\Repository\Builder\Proxy\ProxyBuilderInterface;
use Soliant\SimpleFM\Repository\Repository;
use Soliant\SimpleFM\Repository\RepositoryInterface;

final class RepositoryBuilder implements RepositoryBuilderInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var MetadataBuilderInterface
     */
    private $metadataBuilder;

    /**
     * @var ProxyBuilderInterface
     */
    private $proxyBuilder;

    /**
     * @var RepositoryInterface[]
     */
    private $repositories = [];

    public function __construct(
        ClientInterface $client,
        MetadataBuilderInterface $metadataBuilder,
        ProxyBuilderInterface $proxyBuilder
    ) {
        $this->client = $client;
        $this->metadataBuilder = $metadataBuilder;
        $this->proxyBuilder = $proxyBuilder;
    }

    public function buildRepository(string $entityClassName) : RepositoryInterface
    {
        if (array_key_exists($entityClassName, $this->repositories)) {
            return $this->repositories[$entityClassName];
        }

        $metadata = $this->metadataBuilder->getMetadata($entityClassName);

        return ($this->repositories[$entityClassName] = new Repository(
            $this->client,
            $metadata->getLayout(),
            new MetadataHydration($this, $this->proxyBuilder, $metadata),
            new MetadataExtraction($metadata)
        ));
    }
}
