<?php

namespace App\Enums;

enum InteractionStatus: string
{
    case Planned = 'PLANNED';
    case Completed = 'COMPLETED';
    case Cancelled = 'CANCELLED';
    case FollowUpRequired = 'FOLLOW_UP_REQUIRED';
}
