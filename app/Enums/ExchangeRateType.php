<?php

namespace App\Enums;

enum ExchangeRateType: string
{
    case Spot = 'SPOT';
    case Average = 'AVERAGE';
    case Manual = 'MANUAL';
}
