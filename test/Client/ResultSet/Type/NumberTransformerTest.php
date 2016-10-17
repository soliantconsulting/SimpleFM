<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Client\ResultSet\Type;

use Litipk\BigNumbers\Decimal;
use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Client\ResultSet\Transformer\NumberTransformer;

final class NumberTransformerTest extends TestCase
{
    public function numberProvider() : array
    {
        return [
            ['foo1bar2', '12'],
            ['714/715', '714715'],
            ['7-14/...71.5', '714.715'],
            ['foo-7-14/...71.5', '-714.715'],
        ];
    }

    /**
     * @dataProvider numberProvider
     */
    public function testNumberCleanup(string $fileMakerValue, string $expectedValue)
    {
        $numberTransformer = new NumberTransformer();
        $this->assertEquals($numberTransformer($fileMakerValue), Decimal::fromString($expectedValue));
    }
}
