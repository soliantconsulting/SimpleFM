<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Authentication;

interface IdentityHandlerInterface
{
    public function createIdentity(string $username, string $password) : Identity;

    public function decryptPassword(Identity $identity) : string;
}
