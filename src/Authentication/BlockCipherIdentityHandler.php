<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Authentication;

use Zend\Crypt\BlockCipher;

final class BlockCipherIdentityHandler implements IdentityHandlerInterface
{
    /**
     * @var BlockCipher
     */
    private $blockCipher;

    public function __construct(BlockCipher $blockCipher)
    {
        $this->blockCipher = $blockCipher;
    }

    public function createIdentity(string $username, string $password) : Identity
    {
        return new Identity(
            $username,
            $this->blockCipher->encrypt($password)
        );
    }

    public function decryptPassword(Identity $identity) : string
    {
        return $this->blockCipher->decrypt($identity->getEncryptedPassword());
    }
}
