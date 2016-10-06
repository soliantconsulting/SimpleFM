<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Metadata;

use Assert\Assertion;

final class Entity
{
    /**
     * @var string
     */
    private $layout;

    /**
     * @var string
     */
    private $className;

    /**
     * @var Field[]
     */
    private $fields;

    /**
     * @var OneToMany[]
     */
    private $oneToMany;

    /**
     * @var ManyToOne[]
     */
    private $manyToOne;

    /**
     * @var OneToOne[]
     */
    private $oneToOne;

    public function __construct(
        string $layout,
        string $className,
        array $fields,
        array $oneToMany,
        array $manyToOne,
        array $oneToOne
    ) {
        $this->validateArray($fields, Field::class);
        $this->validateArray($oneToMany, OneToMany::class);
        $this->validateArray($manyToOne, ManyToOne::class);
        $this->validateArray($oneToOne, OneToOne::class);

        $this->layout = $layout;
        $this->className = $className;
        $this->fields = $fields;
        $this->oneToMany = $oneToMany;
        $this->manyToOne = $manyToOne;
        $this->oneToOne = $oneToOne;
    }

    public function getLayout() : string
    {
        return $this->layout;
    }

    public function getClassName() : string
    {
        return $this->className;
    }

    /**
     * @return Field[]
     */
    public function getFields() : array
    {
        return $this->fields;
    }

    /**
     * @return OneToMany[]
     */
    public function getOneToMany() : array
    {
        return $this->oneToMany;
    }

    /**
     * @return ManyToOne[]
     */
    public function getManyToOne() : array
    {
        return $this->manyToOne;
    }

    /**
     * @return OneToOne[]
     */
    public function getOneToOne() : array
    {
        return $this->oneToOne;
    }

    private function validateArray(array $array, string $expectedClassName)
    {
        Assertion::count(array_filter($array, function ($metadata) use ($expectedClassName) : bool {
            return !$metadata instanceof $expectedClassName;
        }), 0, sprintf('At least one element in array is not an instance of %s', $expectedClassName));
    }
}
