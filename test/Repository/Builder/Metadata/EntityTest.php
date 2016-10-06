<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Metadata;

use Assert\InvalidArgumentException;
use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Builder\Metadata\Entity;
use Soliant\SimpleFM\Repository\Builder\Metadata\Field;
use Soliant\SimpleFM\Repository\Builder\Metadata\ManyToOne;
use Soliant\SimpleFM\Repository\Builder\Metadata\OneToMany;
use Soliant\SimpleFM\Repository\Builder\Metadata\OneToOne;
use Soliant\SimpleFM\Repository\Builder\Type\TypeInterface;

final class EntityTest extends TestCase
{
    public function testGenericGetters()
    {
        $fields = [new Field('', '', $this->prophesize(TypeInterface::class)->reveal(), false)];
        $oneToMany = [new OneToMany('', '', '')];
        $manyToOne = [new ManyToOne('', '', '', '')];
        $oneToOne = [new OneToOne('', '', '', false)];

        $metadata = new Entity('layout', 'className', $fields, $oneToMany, $manyToOne, $oneToOne);
        $this->assertSame('layout', $metadata->getLayout());
        $this->assertSame('className', $metadata->getClassName());
        $this->assertSame($fields, $metadata->getFields());
        $this->assertSame($oneToMany, $metadata->getOneToMany());
        $this->assertSame($manyToOne, $metadata->getManyToOne());
        $this->assertSame($oneToOne, $metadata->getOneToOne());
    }

    public function testInvalidField()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('not an instance of %s', Field::class));
        new Entity('layout', 'className', ['foo'], [], [], []);
    }

    public function testInvalidOneToMany()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('not an instance of %s', OneToMany::class));
        new Entity('layout', 'className', [], ['foo'], [], []);
    }

    public function testInvalidManyToOne()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('not an instance of %s', ManyToOne::class));
        new Entity('layout', 'className', [], [], ['foo'], []);
    }

    public function testInvalidOneToOne()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('not an instance of %s', OneToOne::class));
        new Entity('layout', 'className', [], [], [], ['foo']);
    }
}
