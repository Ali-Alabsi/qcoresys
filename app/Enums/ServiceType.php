<?php

namespace App\Enums;

enum ServiceType: string
{
    case Consulting = 'CONSULTING';
    case Development = 'DEVELOPMENT';
    case Support = 'SUPPORT';
    case Maintenance = 'MAINTENANCE';
    case Training = 'TRAINING';
    case Infrastructure = 'INFRASTRUCTURE';
    case Security = 'SECURITY';
    case Cloud = 'CLOUD';
    case Other = 'OTHER';
}
