<?php

declare(strict_types=1);

namespace CodePilots\ChangeSetCollection;

enum ChangeState
{
    case add;
    case edit;
    case remove;
}
