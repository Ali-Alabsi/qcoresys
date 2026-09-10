<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Planning = 'PLANNING';
    case InProgress = 'IN_PROGRESS';
    case OnHold = 'ON_HOLD';
    case Completed = 'COMPLETED';
    case Cancelled = 'CANCELLED';
    case Closed = 'CLOSED';
}
