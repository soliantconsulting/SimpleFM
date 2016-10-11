<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder;

use Assert\Assertion;
use Exception;
use ReflectionClass;
use Soliant\SimpleFM\Repository\Builder\Exception\HydrationException;
use Soliant\SimpleFM\Repository\Builder\Metadata\Entity;
use Soliant\SimpleFM\Repository\HydrationInterface;
use Soliant\SimpleFM\Repository\LazyLoadedCollection;

final class MetadataHydration implements HydrationInterface
{
    /**
     * @var RepositoryBuilderInterface
     */
    private $repositoryBuilder;

    /**
     * @var Entity
     */
    private $entityMetadata;

    public function __construct(RepositoryBuilderInterface $repositoryBuilder, Entity $entityMetadata)
    {
        $this->repositoryBuilder = $repositoryBuilder;
        $this->entityMetadata = $entityMetadata;
    }

    public function hydrateNewEntity(array $data)
    {
        $reflectionClass = new ReflectionClass($this->entityMetadata->getClassName());
        return $this->hydrateExistingEntity($data, $reflectionClass->newInstanceWithoutConstructor());
    }

    public function hydrateExistingEntity(array $data, $entity)
    {
        return $this->hydrateWithMetadata($data, $entity, $this->entityMetadata);
    }

    private function hydrateWithMetadata(array $data, $entity, Entity $metadata)
    {
        Assertion::isInstanceOf($entity, $metadata->getClassName());
        $reflectionClass = new ReflectionClass($entity);

        foreach ($metadata->getFields() as $fieldMetadata) {
            try {
                $type = $fieldMetadata->getType();
                $value = $data[$fieldMetadata->getFieldName()];

                if ($fieldMetadata->isRepeatable()) {
                    Assertion::isArray($value);
                    $value = array_map(function ($value) use ($type) {
                        return $type->fromFileMakerValue($value);
                    }, $value);
                } else {
                    $value = $type->fromFileMakerValue($value);
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
                $embeddableData = $data;
            } else {
                $prefixLength = strlen($prefix);

                foreach ($data as $key => $value) {
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
                $this->hydrateWithMetadata($embeddableData, $embeddable, $embeddableMetadata->getMetadata())
            );
        }

        foreach ($metadata->getOneToMany() as $relationMetadata) {
            $this->setProperty(
                $reflectionClass,
                $entity,
                $relationMetadata->getPropertyName(),
                new LazyLoadedCollection(
                    $this->repositoryBuilder->buildRepository($relationMetadata->getTargetEntity()),
                    $relationMetadata->getTargetFieldName(),
                    $data[$relationMetadata->getTargetTable()]
                )
            );
        }

        $toOne = $metadata->getManyToOne() + $metadata->getOneToOne();

        foreach ($toOne as $relationMetadata) {
            $this->setProperty(
                $reflectionClass,
                $entity,
                $relationMetadata->getPropertyName(),
                (new LazyLoadedCollection(
                    $this->repositoryBuilder->buildRepository($relationMetadata->getTargetEntity()),
                    $relationMetadata->getTargetFieldName(),
                    $data[$relationMetadata->getTargetTable()]
                ))->first()
            );
        }

        if ($metadata->hasRecordId()) {
            $recordIdMetadata = $metadata->getRecordId();
            $this->setProperty($reflectionClass, $entity, $recordIdMetadata->getPropertyName(), $data['record-id']);
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
