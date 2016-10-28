<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Collection;

use Countable;
use Traversable;

interface CollectionInterface extends Countable, Traversable
{
    public function getTotalCount() : int;

    public function isEmpty() : bool;

    public function first();
}
