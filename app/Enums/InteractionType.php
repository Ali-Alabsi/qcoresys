<?php

namespace App\Enums;

enum InteractionType: string
{
    case Call = 'CALL';
    case Email = 'EMAIL';
    case Meeting = 'MEETING';
    case Whatsapp = 'WHATSAPP';
    case Visit = 'VISIT';
    case FollowUp = 'FOLLOW_UP';
    case Consultation = 'CONSULTATION';
    case Other = 'OTHER';
}
