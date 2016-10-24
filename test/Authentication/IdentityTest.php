<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Authentication;

use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Authentication\Identity;

final class IdentityTest extends TestCase
{
    public function testGenericGetters()
    {
        $identity = new Identity('foo', 'bar');
        $this->assertSame('foo', $identity->getUsername());
        $this->assertSame('bar', $identity->getEncryptedPassword());
    }
}
