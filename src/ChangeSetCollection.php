<?php

declare(strict_types=1);

namespace CodePilots\ChangeSetCollection;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use RuntimeException;
use function array_filter;
use function count;

final class ChangeSetCollection implements Countable, IteratorAggregate
{
    /**
     * @var array<array-key, ChangeSetElement>
     */
    private array $elements = [];

    public function __construct(
        iterable $currentCollection,
        iterable $newCollection,
        callable $getId,
        callable $ignore = null
    ) {
        $removeElements = [];
        foreach ($currentCollection as $item) {
            if (null !== $ignore && $ignore($item)) {
                continue;
            }
            $id = $getId($item);
            if (is_object($id) || null === $id) {
                throw new RuntimeException('ID could not be an object or null');
            }
            if (array_key_exists($id, $removeElements)) {
                throw new RuntimeException(sprintf('id "%s" must be unique', (string)$id));
            }
            $removeElements[$id] = new ChangeSetElement(
                element: $item,
                updateData: null,
                changeState: ChangeState::remove
            );
        }

        foreach ($newCollection as $item) {
            $id = $getId($item);
            if (is_object($id)) {
                throw new RuntimeException('ID could not be an object');
            }
            if (null !== $id && isset($removeElements[$id])) {
                $mergeElement = $removeElements[$id];
                $this->elements[$id] = new ChangeSetElement(
                    element: $mergeElement->element,
                    updateData: $item,
                    changeState: ChangeState::edit
                );
                unset($removeElements[$id]);
            } else {
                $this->elements[$id ?? $this->createNewId()] = new ChangeSetElement(
                    element: null,
                    updateData: $item,
                    changeState: ChangeState::add
                );
            }
        }

        $this->elements = array_merge($this->elements, $removeElements);
    }

    private function createNewId(): string
    {
        return uniqid('', true);
    }

    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * @return ArrayIterator<ChangeSetElement>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->elements);
    }

    public function get(mixed $id): ChangeSetElement
    {
        return $this->elements[$id];
    }

    public function countAdd(): int
    {
        return count(array_filter($this->elements, static fn (ChangeSetElement $element) => $element->isAdd()));
    }

    public function countEdit(): int
    {
        return count(array_filter($this->elements, static fn (ChangeSetElement $element) => $element->isEdit()));
    }

    public function countRemove(): int
    {
        return count(array_filter($this->elements, static fn (ChangeSetElement $element) => $element->isRemove()));
    }
}
