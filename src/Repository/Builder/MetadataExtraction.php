<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder;

use Exception;
use ReflectionClass;
use Soliant\SimpleFM\Client\ClientInterface;
use Soliant\SimpleFM\Repository\Builder\Exception\ExtractionException;
use Soliant\SimpleFM\Repository\Builder\Metadata\Entity;
use Soliant\SimpleFM\Repository\Builder\Metadata\ManyToOne;
use Soliant\SimpleFM\Repository\Builder\Metadata\OneToOne;
use Soliant\SimpleFM\Repository\Builder\Proxy\ProxyInterface;
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

    public function extract(object $entity, ClientInterface $client) : array
    {
        return $this->extractWithMetadata($entity, $this->entityMetadata, $client);
    }

    private function extractWithMetadata(object $entity, Entity $metadata, ClientInterface $client) : array
    {
        if ($entity instanceof ProxyInterface) {
            $entity = $entity->__getRealEntity();
        }

        $className = $metadata->getClassName();

        if (! $entity instanceof $className) {
            throw ExtractionException::fromEntityMismatch($className);
        }

        $data = [];
        $reflectionClass = new ReflectionClass($entity);

        foreach ($metadata->getFields() as $fieldMetadata) {
            if ($fieldMetadata->isReadOnly()) {
                continue;
            }

            $fieldName = $fieldMetadata->getFieldName();

            try {
                $type = $fieldMetadata->getType();
                $value = $this->getProperty(
                    $reflectionClass,
                    $entity,
                    $fieldMetadata->getPropertyName()
                );

                if (! $fieldMetadata->isRepeatable()) {
                    $data[$fieldName] = $type->toFileMakerValue($value, $client);
                    continue;
                }

                if (! is_array($value)) {
                    throw ExtractionException::fromNonArrayRepeatable($fieldMetadata->getPropertyName());
                }

                $index = 0;

                foreach ($value as $individualValue) {
                    $data[sprintf('%s(%d)', $fieldName, ++$index)] = $type->toFileMakerValue(
                        $individualValue,
                        $client
                    );
                }
            } catch (Exception $e) {
                throw ExtractionException::fromInvalidField($metadata, $fieldMetadata, $e);
            }
        }

        foreach ($metadata->getEmbeddables() as $embeddableMetadata) {
            $prefix = $embeddableMetadata->getFieldNamePrefix();
            $embeddableData = $this->extractWithMetadata(
                $this->getProperty($reflectionClass, $entity, $embeddableMetadata->getPropertyName()),
                $embeddableMetadata->getMetadata(),
                $client
            );

            foreach ($embeddableData as $key => $value) {
                $data[$prefix . $key] = $value;
            }
        }

        $toOne = array_filter(
            $metadata->getManyToOne(),
            function (ManyToOne $manyToOneMetadata) {
                return !$manyToOneMetadata->isReadOnly();
            }
        ) + array_filter(
            $metadata->getOneToOne(),
            function (OneToOne $oneToOneMetadata) {
                return $oneToOneMetadata->isOwningSide() && !$oneToOneMetadata->isReadOnly();
            }
        );

        foreach ($toOne as $relationMetadata) {
            assert($relationMetadata instanceof ManyToOne || $relationMetadata instanceof OneToOne);

            $relation = $this->getProperty(
                $reflectionClass,
                $entity,
                $relationMetadata->getPropertyName()
            );

            if (null === $relation) {
                $data[$relationMetadata->getFieldName()] = null;
                continue;
            }

            $targetEntity = $relationMetadata->getTargetEntity();

            if ($relation instanceof ProxyInterface) {
                if (! $relation->__getRealEntity() instanceof $targetEntity) {
                    throw ExtractionException::fromEntityMismatch($targetEntity);
                }

                $relationId = $relation->__getRelationId();
            } else {
                if (! $relation instanceof $targetEntity) {
                    throw ExtractionException::fromEntityMismatch($targetEntity);
                }

                $relationId = $this->getProperty(
                    new ReflectionClass($relation),
                    $relation,
                    $relationMetadata->getTargetPropertyName()
                );
            }

            $data[$relationMetadata->getFieldName()] = $relationId;
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
