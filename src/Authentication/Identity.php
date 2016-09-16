<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Authentication;

final class Identity
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $encryptedPassword;

    public function __construct(string $username, string $encryptedPassword)
    {
        $this->username = $username;
        $this->encryptedPassword = $encryptedPassword;
    }

    public function getUsername() : string
    {
        return $this->username;
    }

    public function getEncryptedPassword() : string
    {
        return $this->encryptedPassword;
    }
}
