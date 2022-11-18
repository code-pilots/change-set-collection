<?php

declare(strict_types=1);

namespace CodePilots\ChangeSetCollection;

use Iterator;
use IteratorAggregate;

/**
 * @extends IteratorAggregate<string, ChangeSetElement>
 */
interface ChangeSetElementHashmap extends IteratorAggregate
{
    /** @return Iterator<string, ChangeSetElement> */
    public function getIterator(): Iterator;
}
