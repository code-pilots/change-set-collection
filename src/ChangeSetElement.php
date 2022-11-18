<?php

declare(strict_types=1);

namespace CodePilots\ChangeSetCollection;

final class ChangeSetElement
{
    public function __construct(
        public readonly mixed $element,
        public readonly mixed $updateData,
        public readonly ChangeState $changeState,
    ) {
    }

    public function isAdd(): bool
    {
        return ChangeState::add === $this->changeState;
    }

    public function isEdit(): bool
    {
        return ChangeState::edit === $this->changeState;
    }

    public function isRemove(): bool
    {
        return ChangeState::remove === $this->changeState;
    }
}
