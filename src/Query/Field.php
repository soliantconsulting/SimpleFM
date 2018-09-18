<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Query;

use Soliant\SimpleFM\Query\Exception\InvalidNameException;

final class Field
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $value;

    public function __construct(string $name, string $value, bool $quoteString = true)
    {
        if ('omit' === $name) {
            throw InvalidNameException::fromReservedKeyword('omit');
        }

        $this->name = $name;
        $this->value = $quoteString ? self::quoteString($value) : $value;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getValue() : string
    {
        return $this->value;
    }

    public static function quoteString(string $value) : string
    {
        return strtr($value, [
            '\\' => '\\\\',
            '=' => '\\=',
            '!' => '\\!',
            '<' => '\\<',
            '≤' => '\\≤',
            '>' => '\\>',
            '≥' => '\\≥',
            '…' => '\\…',
            '//' => '\\//',
            '?' => '\\?',
            '@' => '\\@',
            '#' => '\\#',
            '*' => '\\*',
            '"' => '\\"',
            '~' => '\\~',
        ]);
    }
}
