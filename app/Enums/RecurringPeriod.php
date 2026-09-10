<?php

namespace App\Enums;

enum RecurringPeriod: string
{
    case Monthly = 'MONTHLY';
    case Quarterly = 'QUARTERLY';
    case Yearly = 'YEARLY';
}
