<?php

declare(strict_types=1);

use CodePilots\ChangeSetCollection\ChangeSetCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class ChangeSetCollectionTest extends TestCase
{
    /**
     * @dataProvider numericProvider
     */
    public function testNumericIterables(
        iterable $currentCollection,
        iterable $newCollection,
        $counts
    ): void {
        $changeSet = new ChangeSetCollection(
            $currentCollection,
            $newCollection,
            fn (int $value): int => $value
        );

        self::assertEquals($counts[0], $changeSet->countAdd());
        self::assertEquals($counts[1], $changeSet->countEdit());
        self::assertEquals($counts[2], $changeSet->countRemove());
    }

    /**
     * @dataProvider numericProvider
     */
    public function testObjectIterables(
        iterable $currentCollection,
        iterable $newCollection,
        $counts
    ): void {
        $createObject = static function (int $id) {
            return new class($id) {
                public function __construct(
                    public readonly int $id
                ) {
                }
            };
        };

        $currentCollection = array_map($createObject(...), (array)$currentCollection);
        $newCollection = array_map($createObject(...), (array)$newCollection);

        $changeSet = new ChangeSetCollection(
            $currentCollection,
            $newCollection,
            fn (object $object): int => $object->id
        );

        self::assertEquals($counts[0], $changeSet->countAdd());
        self::assertEquals($counts[1], $changeSet->countEdit());
        self::assertEquals($counts[2], $changeSet->countRemove());
    }

    /**
     * @dataProvider numericProvider
     */
    public function testIgnoreItem(): void
    {
        $changeSet = new ChangeSetCollection(
            [1, 2],
            [1, 2],
            fn (int $value): int => $value,
            fn (int $value): bool => 1 === $value,
        );

        self::assertEquals(1, $changeSet->countAdd());
        self::assertEquals(1, $changeSet->countEdit());
        self::assertEquals(0, $changeSet->countRemove());
    }

    /**
     * @dataProvider numericProvider
     */
    public function testIterateElements(): void
    {
        $changeSet = new ChangeSetCollection(
            [],
            [7],
            fn (int $value): int => $value,
        );

        self::assertEquals(1, $changeSet->count());
        foreach ($changeSet as $change) {
            self::assertTrue($change->isAdd());
            self::assertEquals(null, $change->element);
            self::assertEquals(7, $change->updateData);
        }

        $changeSet = new ChangeSetCollection(
            [7],
            [7],
            fn (int $value): int => $value,
        );

        self::assertEquals(1, $changeSet->count());
        foreach ($changeSet as $change) {
            self::assertTrue($change->isEdit());
            self::assertEquals(7, $change->element);
            self::assertEquals(7, $change->updateData);
        }

        $changeSet = new ChangeSetCollection(
            [7],
            [],
            fn (int $value): int => $value,
        );

        self::assertEquals(1, $changeSet->count());
        foreach ($changeSet as $change) {
            self::assertTrue($change->isRemove());
            self::assertEquals(7, $change->element);
            self::assertEquals(null, $change->updateData);
        }
    }

    /**
     * @dataProvider numericProvider
     */
    public function testNonUniqueId(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('id "4" must be unique');
        new ChangeSetCollection(
            [1, 2, 4, 4],
            [5],
            fn (int $value): int => $value,
        );
    }

    /**
     * @dataProvider numericProvider
     */
    public function testInvalidIdNull(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expected "id" of type "scalar", "null" given');
        new ChangeSetCollection(
            [1],
            [1],
            fn (int $value) => null,
        );
    }

    /**
     * @dataProvider numericProvider
     */
    public function testInvalidIdStdClass(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expected "id" of type "scalar", "stdClass" given');
        new ChangeSetCollection(
            [1],
            [1],
            fn (int $value) => (object)['prop' => 'test'],
        );
    }

    private function numericProvider(): array
    {
        return [
            [[1], [1], [0, 1, 0]], // one edit
            [[], [1], [1, 0, 0]], // one add
            [[1], [], [0, 0, 1]], // one remove
            [[1, 5, 7], [1, 10], [1, 1, 2]], // one remove
        ];
    }
}
