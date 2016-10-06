<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder;

use Assert\Assertion;
use ReflectionClass;
use Soliant\SimpleFM\Repository\Builder\Metadata\Entity;
use Soliant\SimpleFM\Repository\Builder\Metadata\OneToOne;
use Soliant\SimpleFM\Repository\ExtractionInterface;

final class MetadataExtraction implements ExtractionInterface
{
    /**
     * @var Entity
     */
    private $entityMetadata;

    public function __construct(Entity $entityMetadata)
    {
        $this->entityMetadata = $entityMetadata;
    }

    public function extract($entity) : array
    {
        Assertion::isInstanceOf($entity, $this->entityMetadata->getClassName());

        $data = [];
        $reflectionClass = new ReflectionClass($entity);

        foreach ($this->entityMetadata->getFields() as $fieldMetadata) {
            $type = $fieldMetadata->getType();
            $value = $this->getProperty(
                $reflectionClass,
                $entity,
                $fieldMetadata->getPropertyName()
            );

            if (!$fieldMetadata->isRepeatable()) {
                $data[$fieldMetadata->getFieldName()] = $type->toFileMakerValue($value);
                continue;
            }

            Assertion::isArray($value);
            $data[$fieldMetadata->getFieldName()] = array_map(function ($value) use ($type) {
                return $type->toFileMakerValue($value);
            }, $value);
        }

        $toOne = $this->entityMetadata->getManyToOne() + array_filter(
            $this->entityMetadata->getOneToOne(),
            function (OneToOne $oneToOneMetadata) {
                return $oneToOneMetadata->isOwningSide();
            }
        );

        foreach ($toOne as $relationMetadata) {
            $relation = $this->getProperty(
                $reflectionClass,
                $entity,
                $relationMetadata->getPropertyName()
            );

            if (null === $relation) {
                $data[$relationMetadata->getFieldName()] = null;
                continue;
            }

            Assertion::isInstanceOf($relation, $relationMetadata->getTargetEntity());

            $data[$relationMetadata->getFieldName()] = $this->getProperty(
                new ReflectionClass($relation),
                $relation,
                $relationMetadata->getTargetPropertyName()
            );
        }

        return $data;
    }

    private function getProperty(ReflectionClass $reflectionClass, $entity, string $propertyName)
    {
        $reflectionProperty = $reflectionClass->getProperty($propertyName);
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->getValue($entity);
    }
}
