<?php

namespace App\Enums;

enum ConsultationStatus: string
{
    case Scheduled = 'SCHEDULED';
    case InProgress = 'IN_PROGRESS';
    case Completed = 'COMPLETED';
    case Cancelled = 'CANCELLED';
}
