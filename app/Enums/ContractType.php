<?php

namespace App\Enums;

enum ContractType: string
{
    case FixedPrice = 'FIXED_PRICE';
    case TimeAndMaterial = 'TIME_AND_MATERIAL';
    case Retainer = 'RETAINER';
    case Maintenance = 'MAINTENANCE';
    case Consulting = 'CONSULTING';
    case Other = 'OTHER';
}
