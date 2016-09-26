<?php
declare(strict_types=1);

namespace SoliantTest\SimpleFM\Repository\Query;

use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Query\Query;

final class QueryTest extends TestCase
{
    public function testWithoutExcludeParameter()
    {
        $query = new Query('foo', 'bar');
        $this->assertSame('foo', $query->getFieldName());
        $this->assertSame('bar', $query->getValue());
        $this->assertFalse($query->isExclude());
    }

    public function testWithExcludeParameter()
    {
        $query = new Query('foo', 'bar', true);
        $this->assertTrue($query->isExclude());
    }
}
