<?php

declare(strict_types=1);

namespace CodePilots\ChangeSetCollection;

use ArrayIterator;
use Countable;
use RuntimeException;
use function array_filter;
use function count;

/**
 * @template TCurrent
 * @template TNew
 * @template TID of scalar|null
 */
final class ChangeSetCollection implements Countable, ChangeSetElementHashmap
{
    /**
     * @var array<string, ChangeSetElement>
     */
    private array $elements = [];

    /**
     * @param iterable<TCurrent> $currentCollection
     * @param iterable<TNew> $newCollection
     * @param callable(TCurrent|TNew):TID $getId
     * @param callable(TCurrent):bool|null $ignore
     */
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
            $id = $this->castIdKey($id);
            if (array_key_exists($id, $removeElements)) {
                throw new RuntimeException(sprintf('id "%s" must be unique', $id));
            }
            $removeElements[$id] = new ChangeSetElement(
                element: $item,
                updateData: null,
                changeState: ChangeState::remove
            );
        }

        foreach ($newCollection as $item) {
            $id = $getId($item) ?? $this->createNewId();
            $id = $this->castIdKey($id);
            if (isset($removeElements[$id])) {
                $mergeElement = $removeElements[$id];
                $this->elements[$id] = new ChangeSetElement(
                    element: $mergeElement->element,
                    updateData: $item,
                    changeState: ChangeState::edit
                );
                unset($removeElements[$id]);
            } else {
                $this->elements[$id] = new ChangeSetElement(
                    element: null,
                    updateData: $item,
                    changeState: ChangeState::add
                );
            }
        }

        $this->elements = array_merge($this->elements, $removeElements);
    }

    private function castIdKey(mixed $id): string
    {
        if (!is_scalar($id)) {
            throw new RuntimeException(sprintf('Expected "id" of type "scalar", "%s" given', get_debug_type($id)));
        }

        return (string)$id;
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
     * @return ArrayIterator<string, ChangeSetElement>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->elements);
    }

    /**
     * @param string $id
     */
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
