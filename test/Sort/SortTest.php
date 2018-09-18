<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Sort;

use PHPUnit\Framework\TestCase;
use Soliant\SimpleFM\Sort\Sort;

final class SortTest extends TestCase
{
    public function testValueObject() : void
    {
        $sort = new Sort('foo', true);
        $this->assertSame('foo', $sort->getFieldName());
        $this->assertTrue($sort->isAscending());
    }
}
