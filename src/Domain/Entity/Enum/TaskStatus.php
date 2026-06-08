<?php

namespace TaskTracker\Domain\Entity\Enum;

enum TaskStatus: int
{
    case TODO = 1;
    case IN_PROGRESS = 2;
    case DONE = 3;
}
