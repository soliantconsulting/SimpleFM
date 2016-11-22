<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder\Metadata;

use Assert\Assertion;
use Cache\Adapter\Common\CacheItem;
use Cache\Adapter\Void\VoidCachePool;
use DOMDocument;
use Psr\Cache\CacheItemPoolInterface;
use SimpleXMLElement;
use Soliant\SimpleFM\Repository\Builder\Metadata\Exception\InvalidFileException;
use Soliant\SimpleFM\Repository\Builder\Metadata\Exception\InvalidTypeException;
use Soliant\SimpleFM\Repository\Builder\Metadata\Exception\MissingInterfaceException;
use Soliant\SimpleFM\Repository\Builder\Type;
use Soliant\SimpleFM\Repository\Builder\Type\TypeInterface;

final class MetadataBuilder implements MetadataBuilderInterface
{
    const SCHEMA_PATH = __DIR__ . '/../../../../xsd/entity-metadata-5-0.xsd';

    /**
     * @var string
     */
    private $xmlFolder;

    /**
     * @var TypeInterface[]
     */
    private $types;

    /**
     * @var Entity[]
     */
    private $metadata = [];

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    public function __construct(string $xmlFolder, array $additionalTypes = [], CacheItemPoolInterface $cache = null)
    {
        if (!empty($additionalTypes)) {
            Assertion::count(array_filter($additionalTypes, function ($type) : bool {
                return !$type instanceof TypeInterface;
            }), 0, sprintf('At least one element in array is not an instance of %s', TypeInterface::class));
        }

        $this->xmlFolder = $xmlFolder;
        $this->types = $additionalTypes + $this->createBuiltInTypes();
        $this->cache = $cache ?: new VoidCachePool();
    }

    public function getMetadata(string $entityClassName) : Entity
    {
        if (array_key_exists($entityClassName, $this->metadata)) {
            return $this->metadata[$entityClassName];
        }

        $cacheKey = sprintf('simplefm.metadata.%s', md5($entityClassName));

        if ($this->cache->hasItem($cacheKey)) {
            return ($this->metadata[$entityClassName] = $this->cache->getItem($cacheKey)->get());
        }

        $xmlPath = sprintf('%s/%s', $this->xmlFolder, $this->buildFilename($entityClassName));

        if (!file_exists($xmlPath)) {
            throw InvalidFileException::fromNonExistentFile($xmlPath, $entityClassName);
        }

        $entityMetadata = $this->buildMetadata($xmlPath);

        $this->cache->save(new CacheItem($cacheKey, true, $entityMetadata));

        return ($this->metadata[$entityClassName] = $entityMetadata);
    }

    private function createBuiltInTypes() : array
    {
        return [
            'boolean' => new Type\BooleanType(),
            'date-time' => new Type\DateTimeType(),
            'date' => new Type\DateType(),
            'decimal' => new Type\DecimalType(),
            'float' => new Type\FloatType(),
            'integer' => new Type\IntegerType(),
            'nullable-string' => new Type\NullableStringType(),
            'stream' => new Type\StreamType(),
            'string' => new Type\StringType(),
            'time' => new Type\TimeType(),
        ];
    }

    private function buildMetadata(string $xmlPath) : Entity
    {
        $xml = $this->loadValidatedXml($xmlPath);
        $fields = [];
        $embeddables = [];
        $oneToMany = [];
        $manyToOne = [];
        $oneToOne = [];
        $recordId = null;

        if (isset($xml->field)) {
            foreach ($xml->field as $field) {
                $type = (string) $field['type'];

                if (!array_key_exists($type, $this->types)) {
                    throw InvalidTypeException::fromNonExistentType($type);
                }

                $fields[] = new Field(
                    (string) $field['name'],
                    (string) $field['property'],
                    $this->types[$type],
                    (isset($field['repeatable']) && (string) $field['repeatable'] === 'true'),
                    (isset($field['read-only']) && (string) $field['read-only'] === 'true')
                );
            }
        }

        if (isset($xml->embeddable)) {
            foreach ($xml->embeddable as $embeddable) {
                $embeddables[] = new Embeddable(
                    (string) $embeddable['property'],
                    (string) $embeddable['field-name-prefix'],
                    $this->getMetadata((string) $embeddable['class-name'])
                );
            }
        }

        if (isset($xml->{'one-to-many'})) {
            foreach ($xml->{'one-to-many'} as $relation) {
                $oneToMany[] = new OneToMany(
                    (string) $relation['property'],
                    (string) $relation['target-table'],
                    (string) $relation['target-entity'],
                    (string) $relation['target-field-name'],
                    (isset($relation['eager-hydration']) && (string) $relation['eager-hydration'] === 'true')
                );
            }
        }

        if (isset($xml->{'many-to-one'})) {
            foreach ($xml->{'many-to-one'} as $relation) {
                $manyToOne[] = new ManyToOne(
                    (string) $relation['name'],
                    (string) $relation['property'],
                    (string) $relation['target-table'],
                    (string) $relation['target-entity'],
                    (string) $relation['target-property-name'],
                    (string) $relation['target-field-name'],
                    $this->getInterfaceNameForRelation(
                        (string) $xml['class-name'],
                        (string) $relation['target-entity']
                    ),
                    (isset($relation['read-only']) && (string) $relation['read-only'] === 'true'),
                    (isset($relation['eager-hydration']) && (string) $relation['eager-hydration'] === 'true')
                );
            }
        }

        if (isset($xml->{'one-to-one-owning'})) {
            foreach ($xml->{'one-to-one-owning'} as $relation) {
                $oneToOne[] = new OneToOne(
                    (string) $relation['property'],
                    (string) $relation['target-table'],
                    (string) $relation['target-entity'],
                    (string) $relation['target-field-name'],
                    $this->getInterfaceNameForRelation(
                        (string) $xml['class-name'],
                        (string) $relation['target-entity']
                    ),
                    true,
                    (isset($relation['read-only']) && (string) $relation['read-only'] === 'true'),
                    (string) $relation['name'],
                    (string) $relation['target-property-name'],
                    (isset($relation['eager-hydration']) && (string) $relation['eager-hydration'] === 'true')
                );
            }
        }

        if (isset($xml->{'one-to-one-inverse'})) {
            foreach ($xml->{'one-to-one-inverse'} as $relation) {
                $oneToOne[] = new OneToOne(
                    (string) $relation['property'],
                    (string) $relation['target-table'],
                    (string) $relation['target-entity'],
                    (string) $relation['target-field-name'],
                    $this->getInterfaceNameForRelation(
                        (string) $xml['class-name'],
                        (string) $relation['target-entity']
                    ),
                    false,
                    false,
                    null,
                    null,
                    (isset($relation['eager-hydration']) && (string) $relation['eager-hydration'] === 'true')
                );
            }
        }

        if (isset($xml->{'record-id'})) {
            $recordId = new RecordId((string) $xml->{'record-id'}['property']);
        }

        return new Entity(
            (string) $xml['layout'],
            (string) $xml['class-name'],
            $fields,
            $embeddables,
            $oneToMany,
            $manyToOne,
            $oneToOne,
            $recordId,
            isset($xml['interface-name']) ? (string) $xml['interface-name'] : null
        );
    }

    private function loadValidatedXml(string $xmlPath) : SimpleXMLElement
    {
        $previousUseInternalErrors = libxml_use_internal_errors(true);
        $xml = new DOMDocument();
        $loadResult = $xml->load($xmlPath);

        if (!$loadResult || !$xml->schemaValidate(self::SCHEMA_PATH)) {
            throw InvalidFileException::fromInvalidFile($xmlPath);
        }

        libxml_use_internal_errors($previousUseInternalErrors);
        return simplexml_import_dom($xml);
    }

    private function buildFilename(string $className) : string
    {
        return str_replace('\\', '.', $className) . '.xml';
    }

    private function getInterfaceNameForRelation(string $mainEntityClassName, string $relationEntityClassName) : string
    {
        $entityMetadata = $this->getMetadata($relationEntityClassName);

        if (!$entityMetadata->hasInterfaceName()) {
            throw MissingInterfaceException::fromMissingInterface($mainEntityClassName, $relationEntityClassName);
        }

        return $entityMetadata->getInterfaceName();
    }
}
