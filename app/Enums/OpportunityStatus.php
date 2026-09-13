<?php

namespace App\Enums;

enum OpportunityStatus: string
{
    case Open = 'OPEN';
    case Won = 'WON';
    case Lost = 'LOST';
    case Cancelled = 'CANCELLED';
}
