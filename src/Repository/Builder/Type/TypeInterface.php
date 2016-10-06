<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Type;

interface TypeInterface
{
    public function fromFileMakerValue($value);

    public function toFileMakerValue($value);
}
