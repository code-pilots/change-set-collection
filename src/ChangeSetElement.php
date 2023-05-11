<?php

declare(strict_types=1);

namespace CodePilots\ChangeSetCollection;

final class ChangeSetElement
{
    public function __construct(
        public readonly mixed $element1,
        public readonly mixed $element2,
        public readonly ChangeState $state,
    ) {
    }

    public function isAdd(): bool
    {
        return ChangeState::add === $this->state;
    }

    public function isEdit(): bool
    {
        return ChangeState::edit === $this->state;
    }

    public function isRemove(): bool
    {
        return ChangeState::remove === $this->state;
    }
}
