<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Metadata;

use Assert\Assertion;
use DOMDocument;
use SimpleXMLElement;
use Soliant\SimpleFM\Repository\Builder\Metadata\Exception\InvalidFileException;
use Soliant\SimpleFM\Repository\Builder\Metadata\Exception\InvalidTypeException;
use Soliant\SimpleFM\Repository\Builder\Type\BooleanType;
use Soliant\SimpleFM\Repository\Builder\Type\DateTimeType;
use Soliant\SimpleFM\Repository\Builder\Type\DecimalType;
use Soliant\SimpleFM\Repository\Builder\Type\FloatType;
use Soliant\SimpleFM\Repository\Builder\Type\IntegerType;
use Soliant\SimpleFM\Repository\Builder\Type\StringType;
use Soliant\SimpleFM\Repository\Builder\Type\TypeInterface;

final class MetadataBuilder implements MetadataBuilderInterface
{
    const SCHEMA_PATH = __DIR__ . '/../../../../docs/xsd/entity-metadata-5-0.xsd';

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

    public function __construct(string $xmlFolder, array $additionalTypes = [])
    {
        if (!empty($additionalTypes)) {
            Assertion::count(array_filter($additionalTypes, function ($type) : bool {
                return !$type instanceof TypeInterface;
            }), 0, sprintf('At least one element in array is not an instance of %s', TypeInterface::class));
        }

        $this->xmlFolder = $xmlFolder;
        $this->types = $additionalTypes + $this->createBuiltInTypes();
    }

    public function getMetadata(string $entityClassName) : Entity
    {
        if (array_key_exists($entityClassName, $this->metadata)) {
            return $this->metadata[$entityClassName];
        }

        $xmlPath = sprintf('%s/%s', $this->xmlFolder, $this->buildFilename($entityClassName));

        if (!file_exists($xmlPath)) {
            throw InvalidFileException::fromNonExistentFile($xmlPath, $entityClassName);
        }

        return ($this->metadata[$entityClassName] = $this->buildMetadata($xmlPath));
    }

    private function createBuiltInTypes() : array
    {
        return [
            'boolean' => new BooleanType(),
            'date-time' => new DateTimeType(),
            'decimal' => new DecimalType(),
            'float' => new FloatType(),
            'integer' => new IntegerType(),
            'string' => new StringType(),
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
                    (string) $relation['target-field-name']
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
                    (isset($relation['read-only']) && (string) $relation['read-only'] === 'true')
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
                    true,
                    (isset($relation['read-only']) && (string) $relation['read-only'] === 'true'),
                    (string) $relation['name'],
                    (string) $relation['target-property-name']
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
                    false,
                    false
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
            $recordId
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
}
