<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Authentication;

use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Authentication\BlockCipherIdentityHandler;
use Soliant\SimpleFM\Authentication\Identity;
use Zend\Crypt\BlockCipher;

final class BlockCipherIdentityHandlerTest extends TestCase
{
    public function testCreateIdentity()
    {
        $blockCipher = $this->prophesize(BlockCipher::class);
        $blockCipher->encrypt('bar')->willReturn('baz');

        $identityHandler = new BlockCipherIdentityHandler($blockCipher->reveal());
        $identity = $identityHandler->createIdentity('foo', 'bar');

        $this->assertSame('foo', $identity->getUsername());
        $this->assertSame('baz', $identity->getEncryptedPassword());
    }

    public function testDecryptPassword()
    {
        $blockCipher = $this->prophesize(BlockCipher::class);
        $blockCipher->decrypt('baz')->willReturn('bar');

        $identityHandler = new BlockCipherIdentityHandler($blockCipher->reveal());
        $this->assertSame('bar', $identityHandler->decryptPassword(new Identity('foo', 'baz')));
    }
}
