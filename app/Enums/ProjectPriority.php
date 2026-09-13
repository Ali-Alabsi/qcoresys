<?php

namespace App\Enums;

enum ProjectPriority: string
{
    case Low = 'LOW';
    case Medium = 'MEDIUM';
    case High = 'HIGH';
    case Critical = 'CRITICAL';
}
