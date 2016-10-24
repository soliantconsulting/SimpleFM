<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Authentication;

use Assert\InvalidArgumentException;
use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Authentication\Identity;
use Soliant\SimpleFM\Authentication\Result;

final class ResultTest extends TestCase
{
    public function testSuccessfulResult()
    {
        $identity = new Identity('foo', 'bar');
        $result = Result::fromIdentity($identity);

        $this->assertTrue($result->isSuccess());
        $this->assertSame($identity, $result->getIdentity());
    }

    public function testUnsuccessfulResult()
    {
        $result = Result::fromInvalidCredentials();

        $this->assertFalse($result->isSuccess());
        $this->expectException(InvalidArgumentException::class);
        $result->getIdentity();
    }
}
