<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Todo = 'TODO';
    case InProgress = 'IN_PROGRESS';
    case Blocked = 'BLOCKED';
    case Done = 'DONE';
    case Cancelled = 'CANCELLED';
}
