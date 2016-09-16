<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Authentication;

use Assert\Assertion;

final class Result
{
    /**
     * @var Identity
     */
    private $identity;

    private function __construct(Identity $identity = null)
    {
        $this->identity = $identity;
    }

    public static function fromIdentity($identity) : self
    {
        return new self($identity);
    }

    public static function fromInvalidCredentials() : self
    {
        return new self();
    }

    public function isSuccess() : bool
    {
        return null !== $this->identity;
    }

    public function getIdentity() : Identity
    {
        Assertion::notNull($this->identity);
        return $this->identity;
    }
}
