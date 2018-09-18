<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Query;

use PHPUnit\Framework\TestCase;
use Soliant\SimpleFM\Query\Exception\InvalidNameException;
use Soliant\SimpleFM\Query\Field;

final class FieldTest extends TestCase
{
    public function testReservedKeyword() : void
    {
        $this->expectException(InvalidNameException::class);
        $this->expectExceptionMessage('is reserved');
        new Field('omit', 'foo');
    }

    public function testGetters() : void
    {
        $field = new Field('foo', 'bar');
        $this->assertSame('foo', $field->getName());
        $this->assertSame('bar', $field->getValue());
    }

    public function testQuotingDisabled() : void
    {
        $field = new Field('foo', '\\=!<≤>≥//?@#*"~', false);
        $this->assertSame('foo', $field->getName());
        $this->assertSame('\\=!<≤>≥//?@#*"~', $field->getValue());
    }

    public function testQuotingEnabled() : void
    {
        $field = new Field('foo', '\\=!<≤>≥//?@#*"~');
        $this->assertSame('foo', $field->getName());
        $this->assertSame('\\\\\\=\\!\\<\\≤\\>\\≥\\//\\?\\@\\#\\*\\"\\~', $field->getValue());
    }
}
