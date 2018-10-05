<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder;

use Exception;
use ReflectionClass;
use Soliant\SimpleFM\Client\ClientInterface;
use Soliant\SimpleFM\Collection\ItemCollection;
use Soliant\SimpleFM\Query\Conditions;
use Soliant\SimpleFM\Query\Field;
use Soliant\SimpleFM\Query\Query;
use Soliant\SimpleFM\Repository\Builder\Exception\HydrationException;
use Soliant\SimpleFM\Repository\Builder\Metadata\Entity;
use Soliant\SimpleFM\Repository\Builder\Proxy\ProxyBuilderInterface;
use Soliant\SimpleFM\Repository\HydrationInterface;
use Soliant\SimpleFM\Repository\LazyLoadedCollection;

final class MetadataHydration implements HydrationInterface
{
    /**
     * @var RepositoryBuilderInterface
     */
    private $repositoryBuilder;

    /**
     * @var ProxyBuilderInterface
     */
    private $proxyBuilder;

    /**
     * @var Entity
     */
    private $entityMetadata;

    public function __construct(
        RepositoryBuilderInterface $repositoryBuilder,
        ProxyBuilderInterface $proxyBuilder,
        Entity $entityMetadata
    ) {
        $this->repositoryBuilder = $repositoryBuilder;
        $this->proxyBuilder = $proxyBuilder;
        $this->entityMetadata = $entityMetadata;
    }

    public function hydrateNewEntity(array $data, ClientInterface $client) : object
    {
        $reflectionClass = new ReflectionClass($this->entityMetadata->getClassName());
        return $this->hydrateExistingEntity($data, $reflectionClass->newInstanceWithoutConstructor(), $client);
    }

    public function hydrateExistingEntity(array $data, object $entity, ClientInterface $client) : object
    {
        return $this->hydrateWithMetadata($data, $entity, $this->entityMetadata, $client);
    }

    private function hydrateWithMetadata(array $data, $entity, Entity $metadata, ClientInterface $client)
    {
        $className = $metadata->getClassName();

        if (! $entity instanceof $className) {
            throw HydrationException::fromEntityMismatch($className);
        }

        $reflectionClass = new ReflectionClass($entity);
        $fieldData = $data['fieldData'] ?? [];
        $portalData = $data['portalData'] ?? [];

        foreach ($metadata->getFields() as $fieldMetadata) {
            try {
                $type = $fieldMetadata->getType();

                if (! array_key_exists($fieldMetadata->getFieldName(), $fieldData)) {
                    throw HydrationException::fromMissingField($fieldMetadata->getFieldName());
                }

                $value = $fieldData[$fieldMetadata->getFieldName()];

                if ($fieldMetadata->isRepeatable()) {
                    if (! is_array($value)) {
                        throw HydrationException::fromNonArrayRepeatable($fieldMetadata->getPropertyName());
                    }

                    $value = array_map(function ($value) use ($type, $client) {
                        return $type->fromFileMakerValue($value, $client);
                    }, $value);
                } else {
                    $value = $type->fromFileMakerValue($value, $client);
                }

                $this->setProperty(
                    $reflectionClass,
                    $entity,
                    $fieldMetadata->getPropertyName(),
                    $value
                );
            } catch (Exception $e) {
                throw HydrationException::fromInvalidField($metadata, $fieldMetadata, $e);
            }
        }

        foreach ($metadata->getEmbeddables() as $embeddableMetadata) {
            $prefix = $embeddableMetadata->getFieldNamePrefix();

            if ('' === $prefix) {
                $embeddableData = $fieldData;
            } else {
                $embeddableData = [];
                $prefixLength = strlen($prefix);

                foreach ($fieldData as $key => $value) {
                    if (0 !== strpos($key, $prefix)) {
                        continue;
                    }

                    $embeddableData[substr($key, $prefixLength)] = $value;
                }
            }

            $reflectionProperty = $reflectionClass->getProperty($embeddableMetadata->getPropertyName());
            $reflectionProperty->setAccessible(true);
            $embeddable = $reflectionProperty->getValue($entity);

            if (null === $embeddable) {
                $embeddable = (new ReflectionClass($embeddableMetadata->getMetadata()->getClassName()))
                    ->newInstanceWithoutConstructor();
            }

            $reflectionProperty->setValue(
                $entity,
                $this->hydrateWithMetadata(
                    ['fieldData' => $embeddableData],
                    $embeddable,
                    $embeddableMetadata->getMetadata(),
                    $client
                )
            );
        }

        foreach ($metadata->getOneToMany() as $relationMetadata) {
            $repository = $this->repositoryBuilder->buildRepository($relationMetadata->getTargetEntity());

            if ($relationMetadata->hasEagerHydration()) {
                $items = [];

                foreach ($portalData[$relationMetadata->getTargetTable()] as $record) {
                    $items[] = $repository->createEntity($record);
                }

                $collection = new ItemCollection($items);
            } else {
                $collection = new LazyLoadedCollection(
                    $repository,
                    $relationMetadata->getTargetFieldName(),
                    $portalData[$relationMetadata->getTargetTable()]
                );
            }

            $this->setProperty($reflectionClass, $entity, $relationMetadata->getPropertyName(), $collection);
        }

        $toOne = $metadata->getManyToOne() + $metadata->getOneToOne();

        foreach ($toOne as $relationMetadata) {
            if (empty($portalData[$relationMetadata->getTargetTable()])) {
                $this->setProperty($reflectionClass, $entity, $relationMetadata->getPropertyName(), null);
                continue;
            }

            $repository = $this->repositoryBuilder->buildRepository($relationMetadata->getTargetEntity());

            if ($relationMetadata->hasEagerHydration()) {
                $this->setProperty(
                    $reflectionClass,
                    $entity,
                    $relationMetadata->getPropertyName(),
                    $repository->createEntity($portalData[$relationMetadata->getTargetTable()][0])
                );
                continue;
            }

            if (! $metadata->hasInterfaceName()) {
                throw HydrationException::fromMissingInterface($metadata->getClassName());
            }

            $fieldName = $relationMetadata->getTargetFieldName();
            $fieldValue = (string) $portalData[$relationMetadata->getTargetTable()][0][$fieldName];

            $proxy = $this->proxyBuilder->createProxy($relationMetadata->getTargetInterfaceName(), function () use (
                $repository,
                $fieldName,
                $fieldValue
            ) {
                return $repository->findOneByQuery(
                    new Query(new Conditions(false, new Field($fieldName, $fieldValue)))
                );
            }, $fieldValue);

            $this->setProperty($reflectionClass, $entity, $relationMetadata->getPropertyName(), $proxy);
        }

        if ($metadata->hasRecordId()) {
            $recordIdMetadata = $metadata->getRecordId();
            $this->setProperty($reflectionClass, $entity, $recordIdMetadata->getPropertyName(), $data['recordId']);
        }

        return $entity;
    }

    private function setProperty(ReflectionClass $reflectionClass, $entity, string $propertyName, $value)
    {
        $reflectionProperty = $reflectionClass->getProperty($propertyName);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($entity, $value);
    }
}
