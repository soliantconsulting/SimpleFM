<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Connection;

use Assert\Assertion;
use DateTimeInterface;
use Litipk\BigNumbers\Decimal;
use Soliant\SimpleFM\Authentication\Identity;
use Soliant\SimpleFM\Connection\Exception\DomainException;

final class Command
{
    /**
     * @var array
     */
    private $parameters;

    /**
     * @var Identity|null
     */
    private $identity;

    public function __construct(string $layout, array $parameters)
    {
        if (array_key_exists('-db', $parameters)) {
            throw DomainException::fromDisallowedParameter('-db');
        }

        if (array_key_exists('-lay', $parameters)) {
            throw DomainException::fromDisallowedParameter('-lay');
        }

        foreach ($parameters as $value) {
            if (!$value instanceof DateTimeInterface
                && !$value instanceof Decimal
                && !is_scalar($value)
                && null !== $value
            ) {
                throw DomainException::fromInvalidValue($value);
            }
        }

        $this->parameters = ['-lay' => $layout] + $parameters;
    }

    public function withIdentity(Identity $identity) : self
    {
        $command = clone $this;
        $command->identity = $identity;

        return $command;
    }

    public function hasIdentity()
    {
        return null !== $this->identity;
    }

    public function getIdentity() : Identity
    {
        Assertion::notNull($this->identity);
        return $this->identity;
    }

    public function getLayout() : string
    {
        return $this->parameters['-lay'];
    }

    public function __toString() : string
    {
        $parts = [];

        foreach ($this->parameters as $name => $value) {
            if ($value instanceof DateTimeInterface) {
                $value = $value->format('m/d/Y H:i:s');
            }

            if ($value instanceof Decimal) {
                $value = (string) $value;
            }

            if (null === $value || '' === $value) {
                $parts[] = urlencode((string) $name);
                continue;
            }

            $parts[] = sprintf('%s=%s', urlencode((string) $name), urlencode((string) $value));
        }

        return implode('&', $parts);
    }
}
