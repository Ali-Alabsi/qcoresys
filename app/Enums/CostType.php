<?php

namespace App\Enums;

enum CostType: string
{
    case Labor = 'LABOR';
    case Software = 'SOFTWARE';
    case Hardware = 'HARDWARE';
    case Hosting = 'HOSTING';
    case Cloud = 'CLOUD';
    case Travel = 'TRAVEL';
    case Transportation = 'TRANSPORTATION';
    case Subcontractor = 'SUBCONTRACTOR';
    case License = 'LICENSE';
    case Other = 'OTHER';
}
