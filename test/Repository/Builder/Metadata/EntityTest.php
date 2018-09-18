<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Metadata;

use PHPUnit\Framework\TestCase;
use Soliant\SimpleFM\Repository\Builder\Metadata\Embeddable;
use Soliant\SimpleFM\Repository\Builder\Metadata\Entity;
use Soliant\SimpleFM\Repository\Builder\Metadata\Exception\InvalidCollectionException;
use Soliant\SimpleFM\Repository\Builder\Metadata\Field;
use Soliant\SimpleFM\Repository\Builder\Metadata\ManyToOne;
use Soliant\SimpleFM\Repository\Builder\Metadata\OneToMany;
use Soliant\SimpleFM\Repository\Builder\Metadata\OneToOne;
use Soliant\SimpleFM\Repository\Builder\Metadata\RecordId;
use Soliant\SimpleFM\Repository\Builder\Type\TypeInterface;

final class EntityTest extends TestCase
{
    public function testGenericGetters() : void
    {
        $fields = [new Field('', '', $this->prophesize(TypeInterface::class)->reveal(), false, false)];
        $embeddables = [new Embeddable('', '', new Entity('', '', [], [], [], [], []))];
        $oneToMany = [new OneToMany('', '', '', '')];
        $manyToOne = [new ManyToOne('', '', '', '', '', '', '', false)];
        $oneToOne = [new OneToOne('', '', '', '', '', false, false)];

        $metadata = new Entity('layout', 'className', $fields, $embeddables, $oneToMany, $manyToOne, $oneToOne);
        $this->assertSame('layout', $metadata->getLayout());
        $this->assertSame('className', $metadata->getClassName());
        $this->assertSame($fields, $metadata->getFields());
        $this->assertSame($embeddables, $metadata->getEmbeddables());
        $this->assertSame($oneToMany, $metadata->getOneToMany());
        $this->assertSame($manyToOne, $metadata->getManyToOne());
        $this->assertSame($oneToOne, $metadata->getOneToOne());
    }

    public function testOptionalRecordId() : void
    {
        $recordId = new RecordId('foo');
        $metadata = new Entity('', '', [], [], [], [], [], $recordId);
        $this->assertTrue($metadata->hasRecordId());
        $this->assertSame($recordId, $metadata->getRecordId());
    }

    public function testMissingRecordId() : void
    {
        $metadata = new Entity('', '', [], [], [], [], []);
        $this->assertFalse($metadata->hasRecordId());
        $this->assertNull($metadata->getRecordId());
    }

    public function testOptionalInterfaceName() : void
    {
        $metadata = new Entity('', '', [], [], [], [], [], null, 'foo');
        $this->assertTrue($metadata->hasInterfaceName());
        $this->assertSame('foo', $metadata->getInterfaceName());
    }

    public function testMissingInterfaceName() : void
    {
        $metadata = new Entity('', '', [], [], [], [], []);
        $this->assertFalse($metadata->hasInterfaceName());
        $this->assertNull($metadata->getInterfaceName());
    }

    public function testInvalidField() : void
    {
        $this->expectException(InvalidCollectionException::class);
        $this->expectExceptionMessage(sprintf('not an instance of %s', Field::class));
        new Entity('layout', 'className', ['foo'], [], [], [], []);
    }

    public function testInvalidEmbeddable() : void
    {
        $this->expectException(InvalidCollectionException::class);
        $this->expectExceptionMessage(sprintf('not an instance of %s', Embeddable::class));
        new Entity('layout', 'className', [], ['foo'], [], [], []);
    }

    public function testInvalidOneToMany() : void
    {
        $this->expectException(InvalidCollectionException::class);
        $this->expectExceptionMessage(sprintf('not an instance of %s', OneToMany::class));
        new Entity('layout', 'className', [], [], ['foo'], [], []);
    }

    public function testInvalidManyToOne() : void
    {
        $this->expectException(InvalidCollectionException::class);
        $this->expectExceptionMessage(sprintf('not an instance of %s', ManyToOne::class));
        new Entity('layout', 'className', [], [], [], ['foo'], []);
    }

    public function testInvalidOneToOne() : void
    {
        $this->expectException(InvalidCollectionException::class);
        $this->expectExceptionMessage(sprintf('not an instance of %s', OneToOne::class));
        new Entity('layout', 'className', [], [], [], [], ['foo']);
    }
}
