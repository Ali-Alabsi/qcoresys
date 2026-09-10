<?php

namespace App\Enums;

enum RequestPriority: string
{
    case Low = 'LOW';
    case Medium = 'MEDIUM';
    case High = 'HIGH';
    case Critical = 'CRITICAL';
}
