<?php

namespace App\Enums;

enum BillingType: string
{
    case Fixed = 'FIXED';
    case Hourly = 'HOURLY';
    case Milestone = 'MILESTONE';
    case Recurring = 'RECURRING';
    case TimeAndMaterial = 'TIME_AND_MATERIAL';
}
