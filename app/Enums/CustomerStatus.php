<?php

namespace App\Enums;

enum CustomerStatus: string
{
    case Lead = 'LEAD';
    case Prospect = 'PROSPECT';
    case Active = 'ACTIVE';
    case Inactive = 'INACTIVE';
    case Suspended = 'SUSPENDED';
    case Closed = 'CLOSED';
}
