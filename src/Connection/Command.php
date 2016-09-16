<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Connection;

use Assert\Assertion;
use DateTimeInterface;
use Soliant\SimpleFM\Connection\Exception\DomainException;

final class Command
{
    /**
     * @var array
     */
    private $parameters;

    /**
     * @var string|null
     */
    private $username;

    /**
     * @var string|null
     */
    private $password;

    public function __construct(string $layout, array $parameters)
    {
        if (array_key_exists('-db', $parameters)) {
            throw DomainException::fromDisallowedParameter('-db');
        }

        if (array_key_exists('-lay', $parameters)) {
            throw DomainException::fromDisallowedParameter('-lay');
        }

        foreach ($parameters as $value) {
            if (!$value instanceof DateTimeInterface && !is_scalar($value) && null !== $value) {
                throw DomainException::fromInvalidValue($value);
            }
        }

        $this->parameters = ['-lay' => $layout] + $parameters;
    }

    public function withCredentials(string $username, string $password) : self
    {
        $command = clone $this;
        $command->username = $username;
        $command->password = $password;

        return $command;
    }

    public function hasCredentials()
    {
        return null !== $this->username && null !== $this->password;
    }

    public function getUsername() : string
    {
        Assertion::notNull($this->username);
        return $this->username;
    }

    public function getPassword() : string
    {
        Assertion::notNull($this->password);
        return $this->password;
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

            if (null === $value || '' === $value) {
                $parts[] = urlencode((string) $name);
                continue;
            }

            $parts[] = sprintf('%s=%s', urlencode((string) $name), urlencode((string) $value));
        }

        return implode('&', $parts);
    }
}
