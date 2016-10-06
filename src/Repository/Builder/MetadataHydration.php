<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder;

use Assert\Assertion;
use ReflectionClass;
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
        Assertion::isInstanceOf($entity, $this->entityMetadata->getClassName());
        $reflectionClass = new ReflectionClass($entity);

        foreach ($this->entityMetadata->getFields() as $fieldMetadata) {
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
        }

        foreach ($this->entityMetadata->getOneToMany() as $relationMetadata) {
            $this->setProperty(
                $reflectionClass,
                $entity,
                $relationMetadata->getPropertyName(),
                new LazyLoadedCollection(
                    $this->repositoryBuilder->buildRepository($relationMetadata->getTargetEntity()),
                    $data[$relationMetadata->getFieldName()]
                )
            );
        }

        $toOne = $this->entityMetadata->getManyToOne() + $this->entityMetadata->getOneToOne();

        foreach ($toOne as $relationMetadata) {
            $this->setProperty(
                $reflectionClass,
                $entity,
                $relationMetadata->getPropertyName(),
                (new LazyLoadedCollection(
                    $this->repositoryBuilder->buildRepository($relationMetadata->getTargetEntity()),
                    $data[$relationMetadata->getFieldName()]
                ))->first()
            );
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
