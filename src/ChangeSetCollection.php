<?php

declare(strict_types=1);

namespace CodePilots\ChangeSetCollection;

use ArrayIterator;
use Countable;
use RuntimeException;
use function array_filter;
use function count;

/**
 * @template T1
 * @template T2
 * @template TID of scalar|null
 */
final class ChangeSetCollection implements Countable, ChangeSetElementHashmap
{
    /**
     * @var array<string, ChangeSetElement>
     */
    private array $elements = [];

    /**
     * @param iterable<T1>           $collection1
     * @param iterable<T2>           $collection2
     * @param callable(T1|T2):TID    $getId
     * @param callable(T1):bool|null $ignore
     */
    public function __construct(
        iterable $collection1,
        iterable $collection2,
        callable $getId,
        callable $ignore = null
    ) {
        $removeElements = [];
        foreach ($collection1 as $element1) {
            if (null !== $ignore && $ignore($element1)) {
                continue;
            }
            $id = $getId($element1);
            $id = $this->castIdKey($id);
            if (array_key_exists($id, $removeElements)) {
                throw new RuntimeException(sprintf('id "%s" must be unique', $id));
            }
            $removeElements[$id] = new ChangeSetElement(
                element1: $element1,
                element2: null,
                changeState: ChangeState::remove
            );
        }

        foreach ($collection2 as $element2) {
            $id = $getId($element2) ?? $this->createNewId();
            $id = $this->castIdKey($id);
            if (isset($removeElements[$id])) {
                $mergeElement = $removeElements[$id];
                $this->elements[$id] = new ChangeSetElement(
                    element1: $mergeElement->element1,
                    element2: $element2,
                    changeState: ChangeState::edit
                );
                unset($removeElements[$id]);
            } else {
                $this->elements[$id] = new ChangeSetElement(
                    element1: null,
                    element2: $element2,
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

    public function countAdded(): int
    {
        return count(array_filter($this->elements, static fn (ChangeSetElement $element) => $element->isAdd()));
    }

    public function countEdited(): int
    {
        return count(array_filter($this->elements, static fn (ChangeSetElement $element) => $element->isEdit()));
    }

    public function countRemoved(): int
    {
        return count(array_filter($this->elements, static fn (ChangeSetElement $element) => $element->isRemove()));
    }
}
