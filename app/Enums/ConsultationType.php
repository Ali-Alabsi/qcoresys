<?php

namespace App\Enums;

enum ConsultationType: string
{
    case Initial = 'INITIAL';
    case Requirements = 'REQUIREMENTS';
    case Technical = 'TECHNICAL';
    case Architecture = 'ARCHITECTURE';
    case Security = 'SECURITY';
    case FollowUp = 'FOLLOW_UP';
    case Other = 'OTHER';
}
